<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Domain\Events\ProductStockUpdatedEvent;
use Shared\Domain\Exceptions\EntityNotFoundException;

class UpdateProductStockAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int|string $productId, int $newStock): bool
    {
        return DB::transaction(function () use ($productId, $newStock) {
            $product = Product::find($productId);
            if (!$product) {
                throw EntityNotFoundException::forEntity('Product', $productId);
            }

            $oldStock = (int) $product->stock;
            $updated = $this->productRepository->updateStock($productId, $newStock);

            if ($updated) {
                event(new ProductStockUpdatedEvent($productId, $oldStock, $newStock));
            }

            return $updated;
        });
    }
}