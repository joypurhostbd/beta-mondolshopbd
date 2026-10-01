<?php

namespace Modules\Order\Domain\Services;

use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Exceptions\InvalidStateTransitionException;

class OrderStateMachine
{
    /**
     * Map of allowed transitions: current_status => [allowed_target_statuses]
     */
    private const ALLOWED_TRANSITIONS = [
        OrderStatusEnum::PENDING->value => [
            OrderStatusEnum::PROCESSING->value,
            OrderStatusEnum::ON_HOLD->value,
            OrderStatusEnum::CANCELLED->value,
        ],
        OrderStatusEnum::PROCESSING->value => [
            OrderStatusEnum::ON_HOLD->value,
            OrderStatusEnum::COMPLETED->value,
            OrderStatusEnum::CANCELLED->value,
        ],
        OrderStatusEnum::ON_HOLD->value => [
            OrderStatusEnum::PROCESSING->value,
            OrderStatusEnum::CANCELLED->value,
        ],
        OrderStatusEnum::COMPLETED->value => [
            OrderStatusEnum::DELIVERED->value,
            OrderStatusEnum::RETURNED->value,
        ],
        OrderStatusEnum::DELIVERED->value => [
            OrderStatusEnum::RETURNED->value,
        ],
        OrderStatusEnum::CANCELLED->value => [],
        OrderStatusEnum::RETURNED->value => [],
    ];

    public function canTransition(OrderStatusEnum $from, OrderStatusEnum $to): bool
    {
        if ($from === $to) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$from->value] ?? [];
        return in_array($to->value, $allowed, true);
    }

    public function assertCanTransition(OrderStatusEnum $from, OrderStatusEnum $to, int|string $orderId = ''): void
    {
        if (!$this->canTransition($from, $to)) {
            throw InvalidStateTransitionException::forTransition(
                "Order [{$orderId}]",
                $from->label(),
                $to->label()
            );
        }
    }
}