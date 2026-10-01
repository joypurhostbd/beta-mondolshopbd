<?php

namespace Modules\Payment\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;

final class PaymentInitiationDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $orderId,
        public readonly string $invoiceId,
        public readonly Money $amount,
        public readonly string $customerName,
        public readonly PhoneNumber $customerPhone,
        public readonly ?string $customerEmail = null,
        public readonly ?string $callbackUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly array $extraParams = []
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['order_id'] ?? 0,
            $data['invoice_id'] ?? '',
            Money::from((float) ($data['amount'] ?? 0)),
            $data['customer_name'] ?? '',
            PhoneNumber::fromString($data['customer_phone'] ?? '01700000000'),
            $data['customer_email'] ?? null,
            $data['callback_url'] ?? null,
            $data['cancel_url'] ?? null,
            $data['extra_params'] ?? []
        );
    }
}