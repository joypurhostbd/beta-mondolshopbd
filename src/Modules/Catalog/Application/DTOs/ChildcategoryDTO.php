<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\ChildcategoryEntity;
use Shared\Application\DTO\DataTransferObject;

class ChildcategoryDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int|string|null $categoryId = null,
        public readonly int|string $subcategoryId = 0,
        public readonly int $status = 1
    ) {}

    public static function fromEntity(ChildcategoryEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            name: $entity->getName(),
            slug: $entity->getSlug(),
            categoryId: $entity->getCategoryId(),
            subcategoryId: $entity->getSubcategoryId(),
            status: $entity->getStatus()
        );
    }
}