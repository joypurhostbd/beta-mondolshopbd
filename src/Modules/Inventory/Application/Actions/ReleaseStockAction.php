<?php

namespace Modules\Inventory\Application\Actions;

use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;

class ReleaseStockAction
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function execute(int|string $productId, int $quantity): bool
    {
        return $this->inventoryRepository->release($productId, $quantity);
    }
}