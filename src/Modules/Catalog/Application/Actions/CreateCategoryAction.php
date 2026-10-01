<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Category;
use Illuminate\Support\Str;
use Modules\Catalog\Application\DTOs\CategoryDTO;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;

class CreateCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(array $data): CategoryDTO
    {
        $slug = $data['slug'] ?? Str::slug($data['name'] ?? 'category');

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $data['parent_id'] ?? 0,
            'status' => $data['status'] ?? 1,
            'front_view' => $data['front_view'] ?? 1,
            'image' => $data['image'] ?? 'public/uploads/category/default.png',
        ]);

        $entity = $this->categoryRepository->findById($category->id);
        return CategoryDTO::fromEntity($entity);
    }
}