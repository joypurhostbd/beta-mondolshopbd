<?php

namespace Modules\Catalog\Infrastructure\Repositories;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Domain\Entities\CategoryEntity;
use Modules\Catalog\Domain\Entities\SubcategoryEntity;
use Modules\Catalog\Domain\Entities\ChildcategoryEntity;
use Shared\Domain\Contracts\EntityInterface;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int|string $id): ?CategoryEntity
    {
        $category = Category::find($id);
        return $category ? $this->toCategoryEntity($category) : null;
    }

    public function findBySlug(string $slug): ?CategoryEntity
    {
        $category = Category::where('slug', $slug)->first();
        return $category ? $this->toCategoryEntity($category) : null;
    }

    public function getActiveRootCategories(): array
    {
        return Category::where('status', 1)
            ->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->get()
            ->map(fn($c) => $this->toCategoryEntity($c))
            ->all();
    }

    public function getSubcategoriesByCategory(int|string $categoryId): array
    {
        return Subcategory::where('category_id', $categoryId)
            ->where('status', 1)
            ->get()
            ->map(fn($s) => new SubcategoryEntity(
                id: $s->id,
                name: $s->subcategoryName ?? $s->name ?? '',
                slug: $s->slug,
                categoryId: $s->category_id,
                status: (int) $s->status
            ))
            ->all();
    }

    public function getChildcategoriesBySubcategory(int|string $subcategoryId): array
    {
        return Childcategory::where('subcategory_id', $subcategoryId)
            ->where('status', 1)
            ->get()
            ->map(fn($c) => new ChildcategoryEntity(
                id: $c->id,
                name: $c->childcategoryName ?? $c->name ?? '',
                slug: $c->slug,
                categoryId: $c->category_id,
                subcategoryId: $c->subcategory_id,
                status: (int) $c->status
            ))
            ->all();
    }

    public function save(EntityInterface $entity): EntityInterface
    {
        /** @var CategoryEntity $catEntity */
        $catEntity = $entity;

        $category = Category::updateOrCreate(
            ['id' => $catEntity->getId()],
            [
                'name' => $catEntity->getName(),
                'slug' => $catEntity->getSlug(),
                'parent_id' => $catEntity->getParentId(),
                'status' => $catEntity->getStatus(),
                'front_view' => $catEntity->isFrontView() ? 1 : 0,
            ]
        );

        return $this->toCategoryEntity($category);
    }

    public function deleteById(int|string $id): bool
    {
        return (bool) Category::destroy($id);
    }

    private function toCategoryEntity(Category $category): CategoryEntity
    {
        return new CategoryEntity(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            parentId: (int) ($category->parent_id ?? 0),
            status: (int) ($category->status ?? 1),
            frontView: (int) ($category->front_view ?? 1)
        );
    }
}