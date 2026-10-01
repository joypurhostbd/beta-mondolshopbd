<?php

namespace Modules\Catalog\Application\Actions;

use Modules\Catalog\Application\DTOs\CategoryDTO;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;

class GetCategoryTreeAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(): array
    {
        $roots = $this->categoryRepository->getActiveRootCategories();
        $tree = [];

        foreach ($roots as $root) {
            $rootDto = CategoryDTO::fromEntity($root);
            $subcategories = $this->categoryRepository->getSubcategoriesByCategory($root->getId());

            $subsArray = [];
            foreach ($subcategories as $sub) {
                $childcategories = $this->categoryRepository->getChildcategoriesBySubcategory($sub->getId());
                $subsArray[] = [
                    'id' => $sub->getId(),
                    'name' => $sub->getName(),
                    'slug' => $sub->getSlug(),
                    'children' => array_map(fn($c) => [
                        'id' => $c->getId(),
                        'name' => $c->getName(),
                        'slug' => $c->getSlug(),
                    ], $childcategories),
                ];
            }

            $tree[] = [
                'id' => $rootDto->id,
                'name' => $rootDto->name,
                'slug' => $rootDto->slug,
                'subcategories' => $subsArray,
            ];
        }

        return $tree;
    }
}