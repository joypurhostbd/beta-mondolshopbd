<?php

namespace Modules\Catalog\Domain\Contracts;

use Modules\Catalog\Domain\Entities\BrandEntity;
use Modules\Catalog\Domain\Entities\ColorEntity;
use Modules\Catalog\Domain\Entities\SizeEntity;

interface AttributeRepositoryInterface
{
    public function getActiveBrands(): array;

    public function getActiveSizes(): array;

    public function getActiveColors(): array;

    public function findBrandById(int|string $id): ?BrandEntity;

    public function findSizeById(int|string $id): ?SizeEntity;

    public function findColorById(int|string $id): ?ColorEntity;
}