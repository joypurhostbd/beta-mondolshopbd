<?php

namespace Modules\Shipping\Domain\Contracts;

use Modules\Shipping\Application\DTOs\CourierParcelDTO;
use Modules\Shipping\Application\DTOs\CourierResponseDTO;
use Modules\Shipping\Application\DTOs\CourierTrackingDTO;

interface CourierGatewayInterface
{
    public function getCourierName(): string;

    public function sendParcel(CourierParcelDTO $dto): CourierResponseDTO;

    public function trackParcel(string $trackingCode): CourierTrackingDTO;
}