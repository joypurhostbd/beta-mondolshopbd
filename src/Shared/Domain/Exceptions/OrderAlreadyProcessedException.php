<?php

namespace Shared\Domain\Exceptions;

class OrderAlreadyProcessedException extends DomainException
{
    public static function forOrder(int|string $orderId, string $status): self
    {
        return new self(
            "Order [{$orderId}] has already been processed with status [{$status}].",
            ['order_id' => $orderId, 'status' => $status],
            409
        );
    }
}