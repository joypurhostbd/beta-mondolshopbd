<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Brand;
use Illuminate\Support\Str;
use Modules\Catalog\Application\DTOs\BrandDTO;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;

class CreateBrandAction
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(array $data): BrandDTO
    {
        $brand = Brand::create([
            'name' => $data['name'],
            'name_bn' => $data['name_bn'] ?? $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'image' => $data['image'] ?? 'public/uploads/brand/default.png',
            'status' => $data['status'] ?? 1,
        ]);

        $entity = $this->attributeRepository->findBrandById($brand->id);
        return BrandDTO::fromEntity($entity);
    }
}