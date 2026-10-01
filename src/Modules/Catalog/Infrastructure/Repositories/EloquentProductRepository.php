<?php

namespace Modules\Catalog\Infrastructure\Repositories;

use App\Models\Product;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Domain\Entities\ProductEntity;
use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Enums\ProductStatusEnum;
use Shared\Domain\ValueObjects\Money;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int|string $id): ?ProductEntity
    {
        $product = Product::find($id);
        if (!$product) {
            return null;
        }

        return $this->toEntity($product);
    }

    public function findBySlug(string $slug): ?ProductEntity
    {
        $product = Product::where('slug', $slug)->first();
        if (!$product) {
            return null;
        }

        return $this->toEntity($product);
    }

    public function search(string $keyword, int $limit = 50): array
    {
        $products = Product::where('name', 'LIKE', '%' . $keyword . '%')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return $products->map(fn($p) => $this->toEntity($p))->all();
    }

    public function updateStock(int|string $id, int $newStock): bool
    {
        return (bool) Product::where('id', $id)->update(['stock' => $newStock]);
    }

    public function save(EntityInterface $entity): EntityInterface
    {
        /** @var ProductEntity $productEntity */
        $productEntity = $entity;

        $product = Product::updateOrCreate(
            ['id' => $productEntity->getId()],
            [
                'name' => $productEntity->getName(),
                'slug' => $productEntity->getSlug(),
                'new_price' => $productEntity->getNewPrice()->getAmount(),
                'old_price' => $productEntity->getOldPrice()?->getAmount(),
                'stock' => $productEntity->getStock(),
                'status' => $productEntity->getStatus()->value === 'active' ? 1 : 0,
                'product_code' => $productEntity->getProductCode(),
            ]
        );

        return $this->toEntity($product);
    }

    public function deleteById(int|string $id): bool
    {
        return (bool) Product::destroy($id);
    }

    private function toEntity(Product $product): ProductEntity
    {
        return new ProductEntity(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            newPrice: Money::from($product->new_price ?? 0),
            oldPrice: $product->old_price ? Money::from($product->old_price) : null,
            stock: (int) ($product->stock ?? 0),
            status: ProductStatusEnum::tryFrom((int) ($product->status ?? 1)) ?? ProductStatusEnum::ACTIVE,
            productCode: $product->product_code
        );
    }
}