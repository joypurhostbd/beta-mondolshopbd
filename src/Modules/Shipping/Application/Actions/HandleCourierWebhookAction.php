<?php

namespace Modules\Shipping\Application\Actions;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class HandleCourierWebhookAction
{
    public function __construct(
        private SyncCourierOrderStatusAction $syncAction
    ) {}

    /**
     * Process incoming courier status update webhook.
     *
     * @param string $provider
     * @param array $payload
     * @return array
     */
    public function execute(string $provider, array $payload): array
    {
        $provider = strtolower($provider);
        $trackingCode = null;
        $consignmentId = null;
        $statusString = null;
        $invoiceId = null;

        // 1. Normalize payload based on courier provider
        if ($provider === 'steadfast') {
            $trackingCode = $payload['tracking_code'] ?? null;
            $consignmentId = $payload['consignment_id'] ?? null;
            $statusString = strtolower($payload['status'] ?? ($payload['notification_type'] ?? ''));
            $invoiceId = $payload['invoice_id'] ?? ($payload['invoice'] ?? null);
        } elseif ($provider === 'pathao') {
            $trackingCode = $payload['consignment_id'] ?? null;
            $statusString = strtolower($payload['order_status'] ?? '');
            $invoiceId = $payload['merchant_order_id'] ?? null;
        } elseif ($provider === 'redx') {
            $trackingCode = $payload['tracking_id'] ?? null;
            $statusString = strtolower($payload['status'] ?? '');
            $invoiceId = $payload['customer_order_id'] ?? null;
        } else {
            // Generic provider fallback
            $trackingCode = $payload['tracking_code'] ?? $payload['consignment_id'] ?? null;
            $consignmentId = $payload['consignment_id'] ?? null;
            $statusString = strtolower($payload['status'] ?? '');
            $invoiceId = $payload['invoice_id'] ?? ($payload['invoice'] ?? null);
        }

        // 2. Find matching Order
        $order = null;
        if ($trackingCode) {
            $order = Order::where('consignment_id', $trackingCode)
                ->orWhere('tracking_code', $trackingCode)
                ->first();
        }

        if (!$order && $consignmentId) {
            $order = Order::where('consignment_id', $consignmentId)
                ->orWhere('tracking_code', $consignmentId)
                ->first();
        }

        if (!$order && $invoiceId) {
            $order = Order::where('invoice_id', $invoiceId)->first();
        }

        if (!$order) {
            Log::warning("Courier webhook: Order not found for tracking {$trackingCode} / consignment {$consignmentId} / invoice {$invoiceId} from {$provider}");
            return [
                'status' => 'ignored',
                'message' => 'Order not found',
            ];
        }

        // 3. Delegate to central SyncCourierOrderStatusAction
        $syncResult = $this->syncAction->execute($order, $statusString, [
            'tracking_code' => $trackingCode,
            'consignment_id' => $consignmentId,
        ]);

        if (!$syncResult['success']) {
            return [
                'status' => 'ignored',
                'message' => $syncResult['message'] ?? 'Could not synchronize status',
            ];
        }

        return [
            'status' => 'success',
            'order_id' => $order->id,
            'invoice_id' => $order->invoice_id,
            'new_status' => (string) $syncResult['order_status'],
            'courier_status' => $syncResult['courier_status'],
        ];
    }
}
