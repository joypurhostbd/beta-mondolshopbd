<?php

namespace Modules\Order\Application\Actions;

use Shared\Domain\Enums\OrderStatusEnum;

class CancelOrderAction
{
    public function __construct(
        private ChangeOrderStatusAction $changeOrderStatusAction
    ) {}

    public function execute(
        int|string $orderId,
        string $reason = 'Customer cancellation request',
        ?string $cancelledBy = null
    ): bool {
        return $this->changeOrderStatusAction->execute(
            $orderId,
            OrderStatusEnum::CANCELLED,
            $reason,
            $cancelledBy
        );
    }
}