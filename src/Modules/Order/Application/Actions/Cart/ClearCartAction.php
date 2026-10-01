<?php

namespace Modules\Order\Application\Actions\Cart;

use Modules\Order\Domain\Contracts\CartRepositoryInterface;

class ClearCartAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    ) {}

    public function execute(string $cartKey): bool
    {
        return $this->cartRepository->delete($cartKey);
    }
}