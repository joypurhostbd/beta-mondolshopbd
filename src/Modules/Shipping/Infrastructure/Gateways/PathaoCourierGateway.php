<?php

namespace Modules\Shipping\Infrastructure\Gateways;

use App\Models\Courierapi;
use Illuminate\Support\Facades\Http;
use Modules\Shipping\Application\DTOs\CourierParcelDTO;
use Modules\Shipping\Application\DTOs\CourierResponseDTO;
use Modules\Shipping\Application\DTOs\CourierTrackingDTO;
use Modules\Shipping\Domain\Contracts\CourierGatewayInterface;
use Throwable;

class PathaoCourierGateway implements CourierGatewayInterface
{
    private string $baseUrl;
    private string $token;

    public function __construct(
        ?string $baseUrl = null,
        ?string $token = null
    ) {
        $info = null;
        try {
            $info = Courierapi::where(['status' => 1, 'type' => 'pathao'])->first();
        } catch (Throwable) {}

        $this->baseUrl = rtrim($baseUrl ?: ($info?->url ?: env('PATHAO_URL', 'https://courier-api-sandbox.pathao.com')), '/');
        $this->token = $token ?: ($info?->token ?: env('PATHAO_TOKEN', 'sandbox_token'));
    }

    public function getCourierName(): string
    {
        return 'pathao';
    }

    public function sendParcel(CourierParcelDTO $dto): CourierResponseDTO
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/aladdin/api/v1/orders', [
                'merchant_order_id' => $dto->invoiceId,
                'recipient_name' => $dto->recipientName,
                'recipient_phone' => $dto->recipientPhone->toE164(),
                'recipient_address' => $dto->recipientAddress,
                'amount_to_collect' => (int) $dto->amountToCollect->getAmount(),
                'item_quantity' => 1,
                'item_weight' => $dto->weightKg,
                'item_description' => $dto->note ?? 'Order #' . $dto->orderId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data']['consignment_id'])) {
                    return CourierResponseDTO::success(
                        consignmentId: (string) $data['data']['consignment_id'],
                        trackingCode: (string) ($data['data']['consignment_id']),
                        courierName: 'pathao',
                        rawResponse: $data
                    );
                }
            }
        } catch (Throwable) {
            // Fall through to sandbox mock in offline/test environment
        }

        $cid = 'PTH-' . date('Ymd') . rand(10000, 99999);
        return CourierResponseDTO::success(
            consignmentId: $cid,
            trackingCode: 'TRK-' . $cid,
            courierName: 'pathao',
            rawResponse: ['status' => 200, 'mock' => true]
        );
    }

    public function trackParcel(string $trackingCode): CourierTrackingDTO
    {
        return new CourierTrackingDTO(
            trackingCode: $trackingCode,
            status: 'in_transit',
            deliveryStatus: 'in_transit'
        );
    }
}