<?php

namespace Modules\Order\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

class CartDTO extends DataTransferObject
{
    /**
     * @param array<string, CartItemDTO> $items
     */
    public function __construct(
        public readonly string $cartKey,
        public readonly array $items,
        public readonly int $itemCount,
        public readonly int $totalQuantity,
        public readonly float $total,
        public readonly string $totalFormatted
    ) {}

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getTotalQuantity(): int
    {
        return $this->totalQuantity;
    }

    public static function fromArray(array $data): static
    {
        $items = [];
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $k => $item) {
                $items[$k] = is_array($item) ? CartItemDTO::fromArray($item) : $item;
            }
        }

        return new self(
            $data['cart_key'] ?? '',
            $items,
            (int) ($data['item_count'] ?? 0),
            (int) ($data['total_quantity'] ?? 0),
            (float) ($data['total'] ?? 0),
            $data['total_formatted'] ?? '0.00'
        );
    }
}