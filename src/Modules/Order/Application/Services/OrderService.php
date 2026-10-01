<?php

namespace Modules\Order\Application\Services;

use Modules\Order\Application\Actions\PlaceOrderAction;
use Modules\Order\Application\DTOs\PlaceOrderInputDTO;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Shared\Domain\Contracts\Modules\OrderModuleInterface;
use Shared\Domain\Enums\OrderStatusEnum;

class OrderService implements OrderModuleInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PlaceOrderAction $placeOrderAction
    ) {}

    public function findOrderById(int|string $orderId): ?array
    {
        $order = $this->orderRepository->findById($orderId);
        return $order ? $order->toArray() : null;
    }

    public function createOrder(array $orderData): array
    {
        $input = PlaceOrderInputDTO::fromArray($orderData);
        $orderDTO = $this->placeOrderAction->execute($input);
        return $orderDTO->toArray();
    }

    public function updateOrderStatus(int|string $orderId, OrderStatusEnum $status): bool
    {
        return $this->orderRepository->updateStatus($orderId, $status);
    }
}