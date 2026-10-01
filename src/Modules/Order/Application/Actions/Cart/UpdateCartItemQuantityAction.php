<?php

namespace Modules\Order\Application\Actions\Cart;

use Modules\Order\Application\DTOs\CartDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Shared\Domain\ValueObjects\Quantity;

class UpdateCartItemQuantityAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    ) {}

    public function execute(string $cartKey, string $itemKey, int $quantity): CartDTO
    {
        $cart = $this->cartRepository->get($cartKey);

        if ($quantity <= 0) {
            $cart->removeItem($itemKey);
        } else {
            $cart->updateItemQuantity($itemKey, Quantity::from($quantity));
        }

        $this->cartRepository->save($cart);

        return CartDTO::fromArray($cart->toArray());
    }
}