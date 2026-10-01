<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = '1';
    case Confirmed = '2';
    case Processing = '3';
    case Shipped = '4';
    case Delivered = '5';
    case Cancelled = '6';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}