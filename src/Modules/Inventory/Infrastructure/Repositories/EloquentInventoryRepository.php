<?php

namespace Modules\Inventory\Infrastructure\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Shared\Domain\Exceptions\InsufficientStockException;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function getAvailableStock(int|string $productId): int
    {
        $product = Product::find($productId);
        if (!$product) {
            throw EntityNotFoundException::forEntity('Product', $productId);
        }

        return (int) ($product->stock ?? 0);
    }

    public function reserve(int|string $productId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            /** @var Product|null $product */
            $product = Product::where('id', $productId)->lockForUpdate()->first();

            if (!$product) {
                throw EntityNotFoundException::forEntity('Product', $productId);
            }

            $currentStock = (int) ($product->stock ?? 0);
            if ($currentStock < $quantity) {
                throw InsufficientStockException::forProduct($productId, $quantity, $currentStock);
            }

            $product->stock = $currentStock - $quantity;
            return $product->save();
        });
    }

    public function release(int|string $productId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            /** @var Product|null $product */
            $product = Product::where('id', $productId)->lockForUpdate()->first();

            if (!$product) {
                throw EntityNotFoundException::forEntity('Product', $productId);
            }

            $product->stock = (int) ($product->stock ?? 0) + $quantity;
            return $product->save();
        });
    }

    public function commit(int|string $productId, int $quantity, ?string $reference = null): bool
    {
        // For committed orders, stock was already reserved / deducted atomically.
        // Record ledger entry if needed.
        return true;
    }

    public function addStock(int|string $productId, int $quantity, ?string $reference = null): bool
    {
        return $this->release($productId, $quantity);
    }
}