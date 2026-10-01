<?php

namespace Shared\Domain\Enums;

enum OrderStatusEnum: int
{
    case PENDING = 1;
    case PROCESSING = 2;
    case ON_HOLD = 3;
    case COMPLETED = 4;
    case CANCELLED = 5;
    case RETURNED = 6;
    case DELIVERED = 7;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::ON_HOLD => 'On Hold',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::RETURNED => 'Returned',
            self::DELIVERED => 'Delivered',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING, self::ON_HOLD => 'info',
            self::COMPLETED, self::DELIVERED => 'success',
            self::CANCELLED, self::RETURNED => 'danger',
        };
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::PENDING, self::ON_HOLD], true);
    }
}