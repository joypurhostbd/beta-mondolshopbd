<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Size;
use Illuminate\Support\Str;
use Modules\Catalog\Application\DTOs\SizeDTO;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;

class CreateSizeAction
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(string $sizeName, ?string $slug = null, int $status = 1): SizeDTO
    {
        $size = Size::create([
            'sizeName' => $sizeName,
            'slug' => $slug ?? Str::slug($sizeName),
            'status' => $status,
        ]);

        $entity = $this->attributeRepository->findSizeById($size->id);
        return SizeDTO::fromEntity($entity);
    }
}