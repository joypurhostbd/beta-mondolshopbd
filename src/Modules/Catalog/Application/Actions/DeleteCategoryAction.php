<?php

namespace Modules\Catalog\Application\Actions;

use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;

class DeleteCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(int|string $categoryId): bool
    {
        $entity = $this->categoryRepository->findById($categoryId);
        if (!$entity) {
            throw EntityNotFoundException::forEntity('Category', $categoryId);
        }

        return $this->categoryRepository->deleteById($categoryId);
    }
}