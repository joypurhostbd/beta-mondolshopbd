<?php

namespace Modules\Shipping\Application\DTOs;

class CourierTrackingDTO
{
    public function __construct(
        public readonly string $trackingCode,
        public readonly string $status,
        public readonly ?string $deliveryStatus = null,
        public readonly array $rawResponse = []
    ) {}
}