<?php

namespace Modules\Shipping\Application\DTOs;

class CourierResponseDTO
{
    public function __construct(
        public readonly bool $isSuccessful,
        public readonly ?string $consignmentId = null,
        public readonly ?string $trackingCode = null,
        public readonly string $courierName = 'courier',
        public readonly ?string $errorMessage = null,
        public readonly array $rawResponse = []
    ) {}

    public static function success(string $consignmentId, string $trackingCode, string $courierName, array $rawResponse = []): self
    {
        return new self(
            isSuccessful: true,
            consignmentId: $consignmentId,
            trackingCode: $trackingCode,
            courierName: $courierName,
            rawResponse: $rawResponse
        );
    }

    public static function failure(string $errorMessage, string $courierName, array $rawResponse = []): self
    {
        return new self(
            isSuccessful: false,
            courierName: $courierName,
            errorMessage: $errorMessage,
            rawResponse: $rawResponse
        );
    }
}