<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;

class DeleteProductAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int|string $productId): bool
    {
        return DB::transaction(function () use ($productId) {
            $product = Product::find($productId);
            if (!$product) {
                throw EntityNotFoundException::forEntity('Product', $productId);
            }

            $product->colors()->detach();
            $product->sizes()->detach();

            return $this->productRepository->deleteById($productId);
        });
    }
}