<?php

namespace Modules\Order\Application\Actions\Cart;

use Modules\Order\Application\DTOs\CartDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;

class RemoveFromCartAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    ) {}

    public function execute(string $cartKey, string $itemKey): CartDTO
    {
        $cart = $this->cartRepository->get($cartKey);
        $cart->removeItem($itemKey);
        $this->cartRepository->save($cart);

        return CartDTO::fromArray($cart->toArray());
    }
}