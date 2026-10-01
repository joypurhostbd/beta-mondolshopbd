<?php

namespace Modules\Order\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

class PlaceOrderInputDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $cartKey,
        public readonly string $customerName,
        public readonly string $phone,
        public readonly string $address,
        public readonly string|int|null $area = null,
        public readonly string $paymentMethod = 'Cash On Delivery',
        public readonly ?int $customerId = null,
        public readonly ?string $discountType = null,
        public readonly ?float $discountValue = null,
        public readonly ?float $shippingCost = null,
        public readonly ?string $note = null,
        public readonly ?string $ipAddress = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['cart_key'] ?? '',
            $data['name'] ?? $data['customer_name'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['area'] ?? null,
            $data['payment_method'] ?? 'Cash On Delivery',
            isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            $data['discount_type'] ?? null,
            isset($data['discount_value']) ? (float) $data['discount_value'] : null,
            isset($data['shipping_cost']) ? (float) $data['shipping_cost'] : null,
            $data['note'] ?? null,
            $data['ip_address'] ?? null
        );
    }
}