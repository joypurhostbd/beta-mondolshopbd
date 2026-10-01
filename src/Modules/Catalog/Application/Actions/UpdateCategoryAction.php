<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Category;
use Modules\Catalog\Application\DTOs\CategoryDTO;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;

class UpdateCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(int|string $categoryId, array $data): CategoryDTO
    {
        $category = Category::find($categoryId);
        if (!$category) {
            throw EntityNotFoundException::forEntity('Category', $categoryId);
        }

        $category->update($data);

        $entity = $this->categoryRepository->findById($category->id);
        return CategoryDTO::fromEntity($entity);
    }
}