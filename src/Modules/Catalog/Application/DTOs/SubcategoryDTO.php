<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\SubcategoryEntity;
use Shared\Application\DTO\DataTransferObject;

class SubcategoryDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int|string $categoryId,
        public readonly int $status = 1
    ) {}

    public static function fromEntity(SubcategoryEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            name: $entity->getName(),
            slug: $entity->getSlug(),
            categoryId: $entity->getCategoryId(),
            status: $entity->getStatus()
        );
    }
}