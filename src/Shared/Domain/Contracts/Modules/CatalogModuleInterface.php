<?php

namespace Shared\Domain\Contracts\Modules;

interface CatalogModuleInterface
{
    /**
     * Find a product by its identifier.
     *
     * @param int|string $productId
     * @return array|null
     */
    public function findProductById(int|string $productId): ?array;

    /**
     * Find a product by its URL slug.
     *
     * @param string $slug
     * @return array|null
     */
    public function findProductBySlug(string $slug): ?array;

    /**
     * Check if a product has sufficient stock available.
     *
     * @param int|string $productId
     * @param int $quantity
     * @return bool
     */
    public function checkStock(int|string $productId, int $quantity): bool;
}