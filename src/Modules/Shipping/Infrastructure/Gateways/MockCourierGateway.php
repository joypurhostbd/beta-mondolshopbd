<?php

namespace Modules\Shipping\Infrastructure\Gateways;

use Modules\Shipping\Application\DTOs\CourierParcelDTO;
use Modules\Shipping\Application\DTOs\CourierResponseDTO;
use Modules\Shipping\Application\DTOs\CourierTrackingDTO;
use Modules\Shipping\Domain\Contracts\CourierGatewayInterface;

class MockCourierGateway implements CourierGatewayInterface
{
    public function __construct(
        private string $courierName = 'mock_courier',
        private bool $shouldSucceed = true
    ) {}

    public function getCourierName(): string
    {
        return $this->courierName;
    }

    public function sendParcel(CourierParcelDTO $dto): CourierResponseDTO
    {
        if (!$this->shouldSucceed) {
            return CourierResponseDTO::failure('Simulated courier error', $this->courierName);
        }

        $cid = strtoupper($this->courierName) . '-' . date('Ymd') . rand(1000, 9999);
        return CourierResponseDTO::success(
            consignmentId: $cid,
            trackingCode: 'TRK-' . $cid,
            courierName: $this->courierName,
            rawResponse: ['status' => 'success']
        );
    }

    public function trackParcel(string $trackingCode): CourierTrackingDTO
    {
        return new CourierTrackingDTO(
            trackingCode: $trackingCode,
            status: 'delivered',
            deliveryStatus: 'delivered'
        );
    }
}