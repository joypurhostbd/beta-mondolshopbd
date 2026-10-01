<?php

namespace Modules\Catalog\Application\DTOs;

use Modules\Catalog\Domain\Entities\BrandEntity;
use Shared\Application\DTO\DataTransferObject;

class BrandDTO extends DataTransferObject
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly ?string $nameBn = null,
        public readonly string $slug = '',
        public readonly ?string $image = null,
        public readonly int $status = 1
    ) {}

    public static function fromEntity(BrandEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            name: $entity->getName(),
            nameBn: $entity->getNameBn(),
            slug: $entity->getSlug(),
            image: $entity->getImage(),
            status: $entity->getStatus()
        );
    }
}