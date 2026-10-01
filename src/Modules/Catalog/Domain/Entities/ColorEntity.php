<?php

namespace Modules\Catalog\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;

class ColorEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $colorName,
        private ?string $color = null,
        private ?string $slug = null,
        private int $status = 1
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getColorName(): string
    {
        return $this->colorName;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}