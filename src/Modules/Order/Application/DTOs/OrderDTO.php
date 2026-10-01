<?php

namespace Modules\Order\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

class OrderDTO extends DataTransferObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $invoiceId,
        public readonly ?int $customerId,
        public readonly float $amount,
        public readonly string $amountFormatted,
        public readonly float $discount,
        public readonly float $shippingCharge,
        public readonly string $orderStatus,
        public readonly string $customerName,
        public readonly string $phone,
        public readonly string $address,
        public readonly array $items = []
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            (int) ($data['id'] ?? 0),
            $data['invoice_id'] ?? '',
            isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            (float) ($data['amount'] ?? 0),
            $data['amount_formatted'] ?? '0.00',
            (float) ($data['discount'] ?? 0),
            (float) ($data['shipping_charge'] ?? 0),
            $data['order_status'] ?? 'pending',
            $data['customer_name'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['items'] ?? []
        );
    }
}