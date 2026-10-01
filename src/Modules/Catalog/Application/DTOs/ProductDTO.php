<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\ProductEntity;
use Shared\Application\DTO\DataTransferObject;

class ProductDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly float $price,
        public readonly ?float $oldPrice = null,
        public readonly int $stock = 0,
        public readonly int|string $status = 1,
        public readonly ?string $productCode = null
    ) {}

    public static function fromEntity(ProductEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            name: $entity->getName(),
            slug: $entity->getSlug(),
            price: $entity->getNewPrice()->getAmount(),
            oldPrice: $entity->getOldPrice()?->getAmount(),
            stock: $entity->getStock(),
            status: $entity->getStatus()->value,
            productCode: $entity->getProductCode()
        );
    }
}