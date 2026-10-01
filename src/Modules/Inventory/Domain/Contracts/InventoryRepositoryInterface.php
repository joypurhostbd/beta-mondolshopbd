<?php

namespace Modules\Inventory\Domain\Contracts;

interface InventoryRepositoryInterface
{
    public function getAvailableStock(int|string $productId): int;

    public function reserve(int|string $productId, int $quantity): bool;

    public function release(int|string $productId, int $quantity): bool;

    public function commit(int|string $productId, int $quantity, ?string $reference = null): bool;

    public function addStock(int|string $productId, int $quantity, ?string $reference = null): bool;
}