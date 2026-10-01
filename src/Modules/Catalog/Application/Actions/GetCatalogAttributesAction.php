<?php

namespace Modules\Catalog\Application\Actions;

use Modules\Catalog\Application\DTOs\BrandDTO;
use Modules\Catalog\Application\DTOs\ColorDTO;
use Modules\Catalog\Application\DTOs\SizeDTO;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;

class GetCatalogAttributesAction
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(): array
    {
        return [
            'brands' => array_map(fn($b) => BrandDTO::fromEntity($b), $this->attributeRepository->getActiveBrands()),
            'sizes' => array_map(fn($s) => SizeDTO::fromEntity($s), $this->attributeRepository->getActiveSizes()),
            'colors' => array_map(fn($c) => ColorDTO::fromEntity($c), $this->attributeRepository->getActiveColors()),
        ];
    }
}