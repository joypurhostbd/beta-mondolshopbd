<?php

namespace Modules\Catalog\Domain\Contracts;

use Modules\Catalog\Domain\Entities\ProductEntity;
use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Contracts\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findById(int|string $id): ?ProductEntity;

    public function findBySlug(string $slug): ?ProductEntity;

    public function search(string $keyword, int $limit = 50): array;

    public function updateStock(int|string $id, int $newStock): bool;
}