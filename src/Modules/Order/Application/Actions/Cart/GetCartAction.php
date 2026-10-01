<?php

namespace Modules\Order\Application\Actions\Cart;

use Modules\Order\Application\DTOs\CartDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;

class GetCartAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    ) {}

    public function execute(string $cartKey): CartDTO
    {
        $cart = $this->cartRepository->get($cartKey);
        return CartDTO::fromArray($cart->toArray());
    }
}