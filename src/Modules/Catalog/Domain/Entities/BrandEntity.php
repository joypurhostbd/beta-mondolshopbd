<?php

namespace Modules\Catalog\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;

class BrandEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $name,
        private ?string $nameBn = null,
        private string $slug = '',
        private ?string $image = null,
        private int $status = 1
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameBn(): ?string
    {
        return $this->nameBn;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}