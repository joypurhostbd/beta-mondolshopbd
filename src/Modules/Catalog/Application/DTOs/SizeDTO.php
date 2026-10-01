<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\SizeEntity;
use Shared\Application\DTO\DataTransferObject;

class SizeDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $sizeName,
        public readonly ?string $slug = null,
        public readonly int $status = 1
    ) {}

    public static function fromEntity(SizeEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            sizeName: $entity->getSizeName(),
            slug: $entity->getSlug(),
            status: $entity->getStatus()
        );
    }
}