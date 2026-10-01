<?php

namespace Modules\Catalog\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;

class CategoryEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $name,
        private string $slug,
        private int $parentId = 0,
        private int $status = 1,
        private int $frontView = 1
    ) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function isFrontView(): bool
    {
        return $this->frontView === 1;
    }

    public function isRootCategory(): bool
    {
        return $this->parentId === 0;
    }
}