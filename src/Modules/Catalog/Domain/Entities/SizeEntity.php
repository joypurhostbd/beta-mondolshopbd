<?php

namespace Modules\Catalog\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;

class SizeEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $sizeName,
        private ?string $slug = null,
        private int $status = 1
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getSizeName(): string
    {
        return $this->sizeName;
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