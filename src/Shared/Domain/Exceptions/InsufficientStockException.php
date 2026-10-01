<?php

namespace Shared\Domain\Exceptions;

class InsufficientStockException extends DomainException
{
    public static function forProduct(int|string $productId, int $requested, int $available): self
    {
        return new self(
            "Insufficient stock for product [{$productId}]. Requested: {$requested}, Available: {$available}.",
            ['product_id' => $productId, 'requested' => $requested, 'available' => $available],
            409
        );
    }
}