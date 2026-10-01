<?php

namespace Modules\Catalog\Application\Services;

use Modules\Catalog\Application\DTOs\ProductDTO;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Shared\Domain\Contracts\Modules\CatalogModuleInterface;

class CatalogService implements CatalogModuleInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function findProductById(int|string $productId): ?array
    {
        $entity = $this->productRepository->findById($productId);
        if (!$entity) {
            return null;
        }

        return ProductDTO::fromEntity($entity)->toArray();
    }

    public function findProductBySlug(string $slug): ?array
    {
        $entity = $this->productRepository->findBySlug($slug);
        if (!$entity) {
            return null;
        }

        return ProductDTO::fromEntity($entity)->toArray();
    }

    public function checkStock(int|string $productId, int $quantity): bool
    {
        $entity = $this->productRepository->findById($productId);
        if (!$entity) {
            return false;
        }

        return $entity->hasStock($quantity);
    }
}