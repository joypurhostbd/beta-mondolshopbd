<?php

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;

class InventoryService implements InventoryModuleInterface
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function reserveStock(int|string $productId, int $quantity): bool
    {
        return $this->inventoryRepository->reserve($productId, $quantity);
    }

    public function releaseStock(int|string $productId, int $quantity): bool
    {
        return $this->inventoryRepository->release($productId, $quantity);
    }

    public function getAvailableStock(int|string $productId): int
    {
        return $this->inventoryRepository->getAvailableStock($productId);
    }
}