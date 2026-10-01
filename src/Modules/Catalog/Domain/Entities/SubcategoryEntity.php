<?php

namespace Modules\Catalog\Domain\Entities;

use Shared\Domain\Contracts\EntityInterface;

class SubcategoryEntity implements EntityInterface
{
    public function __construct(
        private int|string $id,
        private string $name,
        private string $slug,
        private int|string $categoryId,
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCategoryId(): int|string
    {
        return $this->categoryId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}