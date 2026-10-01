<?php

namespace Modules\Shipping\Application\DTOs;

use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;

class CourierParcelDTO
{
    public function __construct(
        public readonly int|string $orderId,
        public readonly string $invoiceId,
        public readonly string $recipientName,
        public readonly PhoneNumber $recipientPhone,
        public readonly string $recipientAddress,
        public readonly Money $amountToCollect,
        public readonly ?string $district = null,
        public readonly float $weightKg = 0.5,
        public readonly ?string $note = null
    ) {}
}