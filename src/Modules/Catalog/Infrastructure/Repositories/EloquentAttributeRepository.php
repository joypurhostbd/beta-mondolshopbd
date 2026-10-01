<?php

namespace Modules\Catalog\Infrastructure\Repositories;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;
use Modules\Catalog\Domain\Entities\BrandEntity;
use Modules\Catalog\Domain\Entities\ColorEntity;
use Modules\Catalog\Domain\Entities\SizeEntity;

class EloquentAttributeRepository implements AttributeRepositoryInterface
{
    public function getActiveBrands(): array
    {
        return Brand::where('status', 1)
            ->get()
            ->map(fn($b) => new BrandEntity(
                id: $b->id,
                name: $b->name,
                nameBn: $b->name_bn,
                slug: $b->slug ?? '',
                image: $b->image,
                status: (int) ($b->status ?? 1)
            ))
            ->all();
    }

    public function getActiveSizes(): array
    {
        return Size::where('status', 1)
            ->get()
            ->map(fn($s) => new SizeEntity(
                id: $s->id,
                sizeName: $s->sizeName,
                slug: $s->slug,
                status: (int) ($s->status ?? 1)
            ))
            ->all();
    }

    public function getActiveColors(): array
    {
        return Color::where('status', 1)
            ->get()
            ->map(fn($c) => new ColorEntity(
                id: $c->id,
                colorName: $c->colorName,
                color: $c->color,
                slug: $c->slug,
                status: (int) ($c->status ?? 1)
            ))
            ->all();
    }

    public function findBrandById(int|string $id): ?BrandEntity
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return null;
        }

        return new BrandEntity(
            id: $brand->id,
            name: $brand->name,
            nameBn: $brand->name_bn,
            slug: $brand->slug ?? '',
            image: $brand->image,
            status: (int) ($brand->status ?? 1)
        );
    }

    public function findSizeById(int|string $id): ?SizeEntity
    {
        $size = Size::find($id);
        if (!$size) {
            return null;
        }

        return new SizeEntity(
            id: $size->id,
            sizeName: $size->sizeName,
            slug: $size->slug,
            status: (int) ($size->status ?? 1)
        );
    }

    public function findColorById(int|string $id): ?ColorEntity
    {
        $color = Color::find($id);
        if (!$color) {
            return null;
        }

        return new ColorEntity(
            id: $color->id,
            colorName: $color->colorName,
            color: $color->color,
            slug: $color->slug,
            status: (int) ($color->status ?? 1)
        );
    }
}