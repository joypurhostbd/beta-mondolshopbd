<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\CategoryEntity;
use Shared\Application\DTO\DataTransferObject;

class CategoryDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int $parentId = 0,
        public readonly int $status = 1,
        public readonly int $frontView = 1
    ) {}

    public static function fromEntity(CategoryEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            name: $entity->getName(),
            slug: $entity->getSlug(),
            parentId: $entity->getParentId(),
            status: $entity->getStatus(),
            frontView: $entity->isFrontView() ? 1 : 0
        );
    }
}