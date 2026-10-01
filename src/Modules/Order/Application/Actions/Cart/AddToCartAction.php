<?php

namespace Modules\Order\Application\Actions\Cart;

use Modules\Order\Application\DTOs\CartDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Entities\CartItemEntity;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;

class AddToCartAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository
    ) {}

    public function execute(
        string $cartKey,
        int|string $productId,
        string $productName,
        float $price,
        int $quantity = 1,
        ?string $size = null,
        ?string $color = null,
        ?string $image = null
    ): CartDTO {
        $cart = $this->cartRepository->get($cartKey);

        $item = new CartItemEntity(
            $productId,
            $productName,
            Money::from($price),
            Quantity::from($quantity),
            $size,
            $color,
            $image
        );

        $cart->addItem($item);
        $this->cartRepository->save($cart);

        return CartDTO::fromArray($cart->toArray());
    }
}