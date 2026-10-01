<?php

namespace Modules\Order\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;

class OrderItemEntity implements EntityInterface
{
    public function __construct(
        private ?int $id,
        private int|string $productId,
        private string $productName,
        private Money $unitPrice,
        private Quantity $quantity,
        private ?Money $purchasePrice = null,
        private ?string $size = null,
        private ?string $color = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int|string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }

    public function getPurchasePrice(): ?Money
    {
        return $this->purchasePrice;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getSubtotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity->getValue());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'sale_price' => $this->unitPrice->getAmount(),
            'purchase_price' => $this->purchasePrice?->getAmount() ?? 0,
            'qty' => $this->quantity->getValue(),
            'product_size' => $this->size,
            'product_color' => $this->color,
            'subtotal' => $this->getSubtotal()->getAmount(),
        ];
    }
}