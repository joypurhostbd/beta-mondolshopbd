<?php

namespace Modules\Order\Application\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Modules\Order\Domain\Events\OrderStatusChangedEvent;
use Modules\Order\Domain\Services\OrderStateMachine;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Exceptions\EntityNotFoundException;

class ChangeOrderStatusAction
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderStateMachine $stateMachine,
        private InventoryModuleInterface $inventoryService
    ) {}

    public function execute(
        int|string $orderId,
        OrderStatusEnum $targetStatus,
        ?string $reason = null,
        ?string $updatedBy = null
    ): bool {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw EntityNotFoundException::forEntity('Order', $orderId);
        }

        $currentStatus = $order->getOrderStatus();

        if ($currentStatus === $targetStatus) {
            return true;
        }

        // 1. Invariant check
        $this->stateMachine->assertCanTransition($currentStatus, $targetStatus, $orderId);

        return DB::transaction(function () use ($order, $orderId, $currentStatus, $targetStatus, $reason, $updatedBy) {
            // 2. Side-effect: If order cancelled or returned, release reserved inventory
            if (in_array($targetStatus, [OrderStatusEnum::CANCELLED, OrderStatusEnum::RETURNED], true)) {
                foreach ($order->getItems() as $item) {
                    $this->inventoryService->releaseStock(
                        $item->getProductId(),
                        $item->getQuantity()->getValue()
                    );
                }
            }

            // 3. Persist status update
            $updated = $this->orderRepository->updateStatus($orderId, $targetStatus);

            if ($updated) {
                // 4. Dispatch event
                Event::dispatch(new OrderStatusChangedEvent(
                    orderId: (int) $orderId,
                    invoiceId: $order->getInvoiceId(),
                    oldStatus: $currentStatus,
                    newStatus: $targetStatus,
                    reason: $reason,
                    updatedBy: $updatedBy
                ));
            }

            return $updated;
        });
    }
}