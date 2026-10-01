<?php

namespace Modules\Order\Domain\Contracts;

use Modules\Order\Domain\Entities\OrderEntity;
use Shared\Domain\Enums\OrderStatusEnum;

interface OrderRepositoryInterface
{
    public function save(OrderEntity $order): OrderEntity;

    public function findById(int|string $id): ?OrderEntity;

    public function findByInvoice(string $invoiceId): ?OrderEntity;

    public function updateStatus(int|string $id, OrderStatusEnum $status): bool;
}