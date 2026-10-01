<?php

namespace Shared\Infrastructure\Views\ViewModels;

use Shared\Domain\ValueObjects\Money;

class ProductCardViewModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly Money $price,
        public readonly ?Money $oldPrice = null,
        public readonly ?string $imageUrl = null,
        public readonly int $stock = 0,
        public readonly ?string $categoryName = null
    ) {}

    public function isDiscounted(): bool
    {
        return $this->oldPrice !== null && $this->oldPrice->isGreaterThan($this->price);
    }

    public function getDiscountPercentage(): int
    {
        if (!$this->isDiscounted() || $this->oldPrice->getAmount() <= 0) {
            return 0;
        }

        $saving = $this->oldPrice->subtract($this->price);
        return (int) round(($saving->getAmount() / $this->oldPrice->getAmount()) * 100);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function getFormattedPrice(): string
    {
        return $this->price->format();
    }

    public function getFormattedOldPrice(): ?string
    {
        return $this->oldPrice?->format();
    }
}