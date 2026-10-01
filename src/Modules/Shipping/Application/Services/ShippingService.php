<?php

namespace Modules\Shipping\Application\Services;

use App\Models\Order;
use App\Models\ShippingCharge;
use Modules\Shipping\Application\DTOs\CourierParcelDTO;
use Shared\Domain\Contracts\Modules\ShippingModuleInterface;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Throwable;

class ShippingService implements ShippingModuleInterface
{
    public function __construct(
        private CourierGatewayManager $courierGatewayManager
    ) {}

    public function calculateShippingCharge(int|string $districtId, Money $subtotal): Money
    {
        try {
            $shipping = ShippingCharge::where('status', 1)
                ->where(function ($q) use ($districtId) {
                    $q->where('id', $districtId)
                      ->orWhere('name', 'LIKE', '%' . $districtId . '%');
                })
                ->first();

            if ($shipping && $shipping->amount !== null) {
                return Money::from((float) $shipping->amount);
            }
        } catch (Throwable) {}

        // Default Dhaka inside 60, outside 120
        return ((int) $districtId === 1 || (int) $districtId === 13) ? Money::from(60.0) : Money::from(120.0);
    }

    public function createShipment(int|string $orderId, array $shippingDetails): array
    {
        $courierName = $shippingDetails['courier'] ?? 'steadfast';
        $gateway = $this->courierGatewayManager->resolve($courierName);

        $dto = new CourierParcelDTO(
            orderId: $orderId,
            invoiceId: $shippingDetails['invoice_id'] ?? 'INV-' . $orderId,
            recipientName: $shippingDetails['name'] ?? 'Customer',
            recipientPhone: PhoneNumber::fromString($shippingDetails['phone'] ?? '01711223344'),
            recipientAddress: $shippingDetails['address'] ?? 'Dhaka, Bangladesh',
            amountToCollect: isset($shippingDetails['amount']) ? Money::from((float) $shippingDetails['amount']) : Money::zero(),
            district: $shippingDetails['district'] ?? 'Dhaka',
            weightKg: (float) ($shippingDetails['weight'] ?? 0.5),
            note: $shippingDetails['note'] ?? null
        );

        $response = $gateway->sendParcel($dto);

        // Update Order consignment info if model exists
        try {
            $order = Order::find($orderId);
            if ($order && $response->isSuccessful) {
                $order->courier_name = $response->courierName;
                $order->courier_status = $response->trackingCode ?? $response->consignmentId;
                $order->save();
            }
        } catch (Throwable) {}

        return [
            'success' => $response->isSuccessful,
            'consignment_id' => $response->consignmentId,
            'tracking_code' => $response->trackingCode,
            'courier' => $response->courierName,
            'error_message' => $response->errorMessage,
            'raw_response' => $response->rawResponse,
        ];
    }
}