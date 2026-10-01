<?php

namespace Modules\Shipping\Application\Actions;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Services\SteadfastCourierService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCourierOrderStatusAction
{
    public function __construct(
        private SteadfastCourierService $steadfastService
    ) {}

    /**
     * Synchronize an order's status using live courier API data or an incoming webhook payload.
     *
     * @param Order $order
     * @param string|null $courierStatus
     * @param array $additionalData
     * @return array
     */
    public function execute(Order $order, ?string $courierStatus = null, array $additionalData = []): array
    {
        $rawStatus = $courierStatus;
        $rawResponse = null;

        // 1. Fetch live status from SteadFast if not provided
        if (empty($rawStatus)) {
            if (!$this->steadfastService->isConfigured()) {
                return [
                    'success' => false,
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice_id,
                    'message' => 'SteadFast Courier API credentials are not configured or active.',
                ];
            }

            if (!empty($order->tracking_code)) {
                $rawResponse = $this->steadfastService->checkDeliveryStatusByTrackingCode($order->tracking_code);
            } elseif (!empty($order->consignment_id)) {
                $rawResponse = $this->steadfastService->checkDeliveryStatusByConsignmentId($order->consignment_id);
            } else {
                $rawResponse = $this->steadfastService->checkDeliveryStatusByInvoice($order->invoice_id);
            }

            $rawStatus = $rawResponse['delivery_status'] ?? ($rawResponse['status'] ?? null);

            if (empty($rawStatus)) {
                return [
                    'success' => false,
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice_id,
                    'message' => $rawResponse['message'] ?? 'Could not retrieve delivery status from SteadFast.',
                    'raw' => $rawResponse,
                ];
            }
        }

        $normalizedStatus = strtolower(trim((string) $rawStatus));

        // Extract tracking / consignment details if available in additional data or response
        $trackingCode = $additionalData['tracking_code'] ?? ($rawResponse['tracking_code'] ?? null);
        $consignmentId = $additionalData['consignment_id'] ?? ($rawResponse['consignment_id'] ?? null);

        // 2. Resolve target internal OrderStatus ID
        $targetStatusId = $this->resolveTargetStatusId($normalizedStatus);

        $statusChanged = false;
        $oldStatusId = (int) $order->order_status;

        // 3. Persist changes in a database transaction
        DB::transaction(function () use (
            $order,
            $normalizedStatus,
            $targetStatusId,
            $trackingCode,
            $consignmentId,
            $oldStatusId,
            &$statusChanged
        ) {
            $order->courier_name = 'steadfast';
            $order->courier_status = $normalizedStatus;

            if ($trackingCode && empty($order->tracking_code)) {
                $order->tracking_code = (string) $trackingCode;
            }
            if ($consignmentId && empty($order->consignment_id)) {
                $order->consignment_id = (string) $consignmentId;
            }

            if ($targetStatusId !== null && $oldStatusId !== (int) $targetStatusId) {
                $order->order_status = $targetStatusId;
                $statusChanged = true;

                $this->syncStockAndPayment($order, $oldStatusId, (int) $targetStatusId, $normalizedStatus);
            }

            $order->save();
        });

        return [
            'success' => true,
            'order_id' => $order->id,
            'invoice_id' => $order->invoice_id,
            'courier_name' => 'steadfast',
            'courier_status' => $normalizedStatus,
            'order_status' => (int) $order->order_status,
            'status_changed' => $statusChanged,
            'message' => "Order #{$order->invoice_id} courier status is '{$normalizedStatus}'.",
            'raw' => $rawResponse,
        ];
    }

    /**
     * Map courier delivery status string to the system's dynamic OrderStatus ID.
     */
    private function resolveTargetStatusId(string $courierStatus): ?int
    {
        return match ($courierStatus) {
            'delivered', 'partial_delivered', 'completed', 'successful' =>
                (int) (OrderStatus::whereIn('slug', ['completed', 'delivered'])->value('id') ?? OrderStatusEnum::Delivered->value),

            'cancelled', 'canceled', 'returned', 'return', 'cancelled_approval_pending' =>
                (int) (OrderStatus::where('slug', 'cancelled')->value('id') ?? OrderStatusEnum::Cancelled->value),

            'hold' =>
                (int) (OrderStatus::whereIn('slug', ['on-hold', 'hold'])->value('id') ?? 4),

            'in_review', 'pending', 'in_transit', 'shipped', 'picked' =>
                (int) (OrderStatus::whereIn('slug', ['in-courier', 'shipped', 'on-the-way'])->value('id') ?? OrderStatusEnum::Shipped->value),

            default => null,
        };
    }

    /**
     * Synchronize product stock and order/payment records on status transitions.
     */
    private function syncStockAndPayment(Order $order, int $oldStatusId, int $newStatusId, string $courierStatus): void
    {
        $completedStatusIds = OrderStatus::whereIn('slug', ['completed', 'delivered'])->pluck('id')->map(fn($v) => (int)$v)->toArray();
        $cancelledStatusIds = OrderStatus::where('slug', 'cancelled')->pluck('id')->map(fn($v) => (int)$v)->toArray();

        // Fallbacks to OrderStatusEnum if table is empty or unseeded
        $completedStatusIds[] = (int) OrderStatusEnum::Delivered->value;
        $cancelledStatusIds[] = (int) OrderStatusEnum::Cancelled->value;

        $isCourierCancelled = in_array($courierStatus, ['cancelled', 'canceled', 'returned', 'return', 'cancelled_approval_pending'], true);
        $isCourierCompleted = in_array($courierStatus, ['delivered', 'partial_delivered', 'completed', 'successful'], true);

        $isNewCompleted = $isCourierCompleted || in_array($newStatusId, $completedStatusIds, true);
        $isOldCompleted = in_array($oldStatusId, $completedStatusIds, true);
        $isNewCancelled = $isCourierCancelled || in_array($newStatusId, $cancelledStatusIds, true);
        $isOldCancelled = in_array($oldStatusId, $cancelledStatusIds, true);

        if ($isNewCompleted && !$isOldCompleted) {
            // Deduct stock if completing for the first time
            $details = OrderDetails::where('order_id', $order->id)->get(['id', 'product_id', 'qty']);
            foreach ($details as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->stock = max(0, $product->stock - (int) $item->qty);
                    $product->save();
                }
            }

            $order->payment_status = 'paid';
            Payment::where('order_id', $order->id)->update(['payment_status' => 'paid']);
        } elseif ($isNewCancelled && !$isOldCancelled) {
            // Release/restore stock back to inventory if order was previously completed or deducted
            $details = OrderDetails::where('order_id', $order->id)->get(['id', 'product_id', 'qty']);
            foreach ($details as $item) {
                Product::where('id', $item->product_id)->increment('stock', (int) $item->qty);
            }

            $order->payment_status = 'cancelled';
            Payment::where('order_id', $order->id)->update(['payment_status' => 'cancelled']);
        }
    }
}
