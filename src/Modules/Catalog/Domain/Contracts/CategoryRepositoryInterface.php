<?php

namespace Modules\Catalog\Domain\Contracts;

use Modules\Catalog\Domain\Entities\CategoryEntity;
use Modules\Catalog\Domain\Entities\SubcategoryEntity;
use Modules\Catalog\Domain\Entities\ChildcategoryEntity;
use Shared\Domain\Contracts\RepositoryInterface;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function findById(int|string $id): ?CategoryEntity;

    public function findBySlug(string $slug): ?CategoryEntity;

    public function getActiveRootCategories(): array;

    public function getSubcategoriesByCategory(int|string $categoryId): array;

    public function getChildcategoriesBySubcategory(int|string $subcategoryId): array;
}