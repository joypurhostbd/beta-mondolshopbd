<?php

namespace Modules\Order\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

class PriceBreakdownDTO extends DataTransferObject
{
    public function __construct(
        public readonly float $subtotal,
        public readonly string $subtotalFormatted,
        public readonly float $discountAmount,
        public readonly string $discountAmountFormatted,
        public readonly float $shippingCharge,
        public readonly string $shippingChargeFormatted,
        public readonly float $taxAmount,
        public readonly string $taxAmountFormatted,
        public readonly float $grandTotal,
        public readonly string $grandTotalFormatted,
        public readonly int $itemCount,
        public readonly int $totalQuantity
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            (float) ($data['subtotal'] ?? 0),
            $data['subtotal_formatted'] ?? '0.00',
            (float) ($data['discount_amount'] ?? 0),
            $data['discount_amount_formatted'] ?? '0.00',
            (float) ($data['shipping_charge'] ?? 0),
            $data['shipping_charge_formatted'] ?? '0.00',
            (float) ($data['tax_amount'] ?? 0),
            $data['tax_amount_formatted'] ?? '0.00',
            (float) ($data['grand_total'] ?? 0),
            $data['grand_total_formatted'] ?? '0.00',
            (int) ($data['item_count'] ?? 0),
            (int) ($data['total_quantity'] ?? 0)
        );
    }
}