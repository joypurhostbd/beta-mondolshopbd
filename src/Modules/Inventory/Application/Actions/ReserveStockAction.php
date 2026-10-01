<?php

namespace Modules\Inventory\Application\Actions;

use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;

class ReserveStockAction
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function execute(int|string $productId, int $quantity): bool
    {
        return $this->inventoryRepository->reserve($productId, $quantity);
    }
}