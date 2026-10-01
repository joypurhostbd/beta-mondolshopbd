<?php

namespace Shared\Domain\Enums;

enum PaymentMethodEnum: string
{
    case COD = 'cod';
    case BKASH = 'bkash';
    case SHURJOPAY = 'shurjopay';
    case NAGAD = 'nagad';
    case ROCKET = 'rocket';

    public function label(): string
    {
        return match ($this) {
            self::COD => 'Cash on Delivery',
            self::BKASH => 'bKash',
            self::SHURJOPAY => 'ShurjoPay',
            self::NAGAD => 'Nagad',
            self::ROCKET => 'Rocket',
        };
    }

    public function isOnline(): bool
    {
        return $this !== self::COD;
    }
}