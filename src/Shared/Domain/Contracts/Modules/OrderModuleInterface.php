<?php

namespace Shared\Domain\Contracts\Modules;

use Shared\Domain\Enums\OrderStatusEnum;

interface OrderModuleInterface
{
    /**
     * Find an order by its unique identifier.
     *
     * @param int|string $orderId
     * @return array|null
     */
    public function findOrderById(int|string $orderId): ?array;

    /**
     * Create a new order across boundaries.
     *
     * @param array $orderData
     * @return array
     */
    public function createOrder(array $orderData): array;

    /**
     * Update the lifecycle state of an order.
     *
     * @param int|string $orderId
     * @param OrderStatusEnum $status
     * @return bool
     */
    public function updateOrderStatus(int|string $orderId, OrderStatusEnum $status): bool;
}