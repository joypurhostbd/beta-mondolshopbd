<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Color;
use Illuminate\Support\Str;
use Modules\Catalog\Application\DTOs\ColorDTO;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;

class CreateColorAction
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(string $colorName, ?string $colorHex = null, ?string $slug = null, int $status = 1): ColorDTO
    {
        $color = Color::create([
            'colorName' => $colorName,
            'color' => $colorHex ?? '#000000',
            'slug' => $slug ?? Str::slug($colorName),
            'status' => $status,
        ]);

        $entity = $this->attributeRepository->findColorById($color->id);
        return ColorDTO::fromEntity($entity);
    }
}