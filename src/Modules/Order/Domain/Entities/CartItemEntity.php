<?php

namespace Modules\Order\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;

class CartItemEntity implements EntityInterface
{
    public function __construct(
        private int|string $productId,
        private string $productName,
        private Money $price,
        private Quantity $quantity,
        private ?string $size = null,
        private ?string $color = null,
        private ?string $image = null
    ) {}

    public function getId(): string
    {
        return $this->getItemKey();
    }

    public function getItemKey(): string
    {
        $sizeKey = $this->size ? trim(strtolower($this->size)) : 'none';
        $colorKey = $this->color ? trim(strtolower($this->color)) : 'none';
        return "item_{$this->productId}_{$sizeKey}_{$colorKey}";
    }

    public function getProductId(): int|string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getSubtotal(): Money
    {
        return $this->price->multiply($this->quantity->getValue());
    }

    public function withQuantity(Quantity $quantity): self
    {
        return new self(
            $this->productId,
            $this->productName,
            $this->price,
            $quantity,
            $this->size,
            $this->color,
            $this->image
        );
    }

    public function toArray(): array
    {
        return [
            'item_key' => $this->getItemKey(),
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'price' => $this->price->getAmount(),
            'price_formatted' => $this->price->format(),
            'quantity' => $this->quantity->getValue(),
            'size' => $this->size,
            'color' => $this->color,
            'image' => $this->image,
            'subtotal' => $this->getSubtotal()->getAmount(),
            'subtotal_formatted' => $this->getSubtotal()->format(),
        ];
    }
}