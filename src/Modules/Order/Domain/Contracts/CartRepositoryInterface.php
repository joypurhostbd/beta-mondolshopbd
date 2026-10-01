<?php

namespace Modules\Order\Domain\Contracts;

use Modules\Order\Domain\Entities\CartEntity;

interface CartRepositoryInterface
{
    public function get(string $cartKey): CartEntity;

    public function save(CartEntity $cart, int $ttlSeconds = 2592000): bool;

    public function delete(string $cartKey): bool;
}