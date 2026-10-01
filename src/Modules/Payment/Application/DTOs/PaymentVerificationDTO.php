<?php

namespace Modules\Payment\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

final class PaymentVerificationDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $gatewayTransactionId,
        public readonly int|string|null $orderId = null,
        public readonly array $payload = []
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['gateway_transaction_id'] ?? $data['sp_order_id'] ?? $data['paymentID'] ?? '',
            $data['order_id'] ?? null,
            $data['payload'] ?? $data
        );
    }
}