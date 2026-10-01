<?php

namespace Modules\Order\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

class CartItemDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $itemKey,
        public readonly int|string $productId,
        public readonly string $productName,
        public readonly float $price,
        public readonly string $priceFormatted,
        public readonly int $quantity,
        public readonly ?string $size,
        public readonly ?string $color,
        public readonly ?string $image,
        public readonly float $subtotal,
        public readonly string $subtotalFormatted
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            $data['item_key'] ?? '',
            $data['product_id'] ?? 0,
            $data['product_name'] ?? '',
            (float) ($data['price'] ?? 0),
            $data['price_formatted'] ?? '0.00',
            (int) ($data['quantity'] ?? 1),
            $data['size'] ?? null,
            $data['color'] ?? null,
            $data['image'] ?? null,
            (float) ($data['subtotal'] ?? 0),
            $data['subtotal_formatted'] ?? '0.00'
        );
    }
}