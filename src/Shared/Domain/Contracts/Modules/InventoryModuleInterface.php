<?php

namespace Shared\Domain\Contracts\Modules;

interface InventoryModuleInterface
{
    /**
     * Reserve inventory stock for pending order.
     *
     * @param int|string $productId
     * @param int $quantity
     * @return bool
     */
    public function reserveStock(int|string $productId, int $quantity): bool;

    /**
     * Release reserved inventory stock.
     *
     * @param int|string $productId
     * @param int $quantity
     * @return bool
     */
    public function releaseStock(int|string $productId, int $quantity): bool;
}