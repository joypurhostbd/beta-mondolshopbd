<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\ColorEntity;
use Shared\Application\DTO\DataTransferObject;

class ColorDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $colorName,
        public readonly ?string $color = null,
        public readonly ?string $slug = null,
        public readonly int $status = 1
    ) {}

    public static function fromEntity(ColorEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            colorName: $entity->getColorName(),
            color: $entity->getColor(),
            slug: $entity->getSlug(),
            status: $entity->getStatus()
        );
    }
}