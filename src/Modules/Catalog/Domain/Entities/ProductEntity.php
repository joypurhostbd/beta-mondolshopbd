<?php

namespace Modules\Catalog\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Enums\ProductStatusEnum;
use Shared\Domain\ValueObjects\Money;

class ProductEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $name,
        private string $slug,
        private Money $newPrice,
        private ?Money $oldPrice = null,
        private int $stock = 0,
        private ProductStatusEnum $status = ProductStatusEnum::ACTIVE,
        private ?string $productCode = null
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getNewPrice(): Money
    {
        return $this->newPrice;
    }

    public function getOldPrice(): ?Money
    {
        return $this->oldPrice;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getStatus(): ProductStatusEnum
    {
        return $this->status;
    }

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function isAvailable(): bool
    {
        return $this->status === ProductStatusEnum::ACTIVE && $this->stock > 0;
    }

    public function hasStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }
}