<?php

namespace Modules\Promotion\Domain\Contracts;

use Modules\Promotion\Domain\Entities\ReviewEntity;

interface ReviewRepositoryInterface
{
    public function findByProduct(int $productId): array;
    public function save(ReviewEntity $entity): ReviewEntity;
    public function updateStatus(int $id, string $status): bool;
}