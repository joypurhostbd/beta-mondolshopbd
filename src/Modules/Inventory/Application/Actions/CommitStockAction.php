<?php

namespace Modules\Inventory\Application\Actions;

use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;

class CommitStockAction
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function execute(int|string $productId, int $quantity, ?string $reference = null): bool
    {
        return $this->inventoryRepository->commit($productId, $quantity, $reference);
    }
}