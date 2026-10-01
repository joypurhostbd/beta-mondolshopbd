<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Order\Application\Actions\Cart\AddToCartAction;
use Modules\Order\Application\Actions\Cart\ClearCartAction;
use Modules\Order\Application\Actions\Cart\GetCartAction;
use Modules\Order\Application\Actions\Cart\RemoveFromCartAction;
use Modules\Order\Application\Actions\Cart\UpdateCartItemQuantityAction;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Entities\CartEntity;
use Modules\Order\Domain\Entities\CartItemEntity;
use Modules\Order\Infrastructure\Repositories\RedisCartRepository;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;
use Tests\TestCase;

class RedisCartTest extends TestCase
{
    use RefreshDatabase;

    private CartRepositoryInterface $cartRepo;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->cartRepo = $this->app->make(CartRepositoryInterface::class);
    }

    public function test_cart_item_and_cart_entity_calculations(): void
    {
        $item1 = new CartItemEntity(101, 'Cotton Shirt', Money::from(500), Quantity::from(2), 'L', 'Blue');
        $this->assertEquals(1000.0, $item1->getSubtotal()->getAmount());
        $this->assertEquals('item_101_l_blue', $item1->getItemKey());

        $item2 = new CartItemEntity(102, 'Denim Jeans', Money::from(1200), Quantity::from(1), '32', 'Black');

        $cart = new CartEntity('cart_test_session_1');
        $cart->addItem($item1);
        $cart->addItem($item2);

        $this->assertEquals(2, $cart->getItemCount());
        $this->assertEquals(3, $cart->getTotalQuantity());
        $this->assertEquals(2200.0, $cart->getTotal()->getAmount());

        // Adding identical item merges quantity
        $item1Duplicate = new CartItemEntity(101, 'Cotton Shirt', Money::from(500), Quantity::from(1), 'L', 'Blue');
        $cart->addItem($item1Duplicate);

        $this->assertEquals(2, $cart->getItemCount());
        $this->assertEquals(4, $cart->getTotalQuantity());
        $this->assertEquals(2700.0, $cart->getTotal()->getAmount());
    }

    public function test_redis_cart_actions_full_lifecycle(): void
    {
        $cartKey = 'guest_session_xyz_789';

        $addAction = new AddToCartAction($this->cartRepo);
        $cartDTO = $addAction->execute($cartKey, 1, 'Leather Wallet', 450.0, 2, null, 'Brown');

        $this->assertEquals(1, $cartDTO->itemCount);
        $this->assertEquals(2, $cartDTO->totalQuantity);
        $this->assertEquals(900.0, $cartDTO->total);

        // Add second item
        $cartDTO = $addAction->execute($cartKey, 2, 'Travel Bag', 1500.0, 1);
        $this->assertEquals(2, $cartDTO->itemCount);
        $this->assertEquals(3, $cartDTO->totalQuantity);
        $this->assertEquals(2400.0, $cartDTO->total);

        // Update quantity
        $updateAction = new UpdateCartItemQuantityAction($this->cartRepo);
        $firstItemKey = array_key_first($cartDTO->items);
        $cartDTO = $updateAction->execute($cartKey, $firstItemKey, 3);
        $this->assertEquals(4, $cartDTO->totalQuantity);

        // Get Cart
        $getAction = new GetCartAction($this->cartRepo);
        $retrievedCart = $getAction->execute($cartKey);
        $this->assertEquals(2, $retrievedCart->itemCount);

        // Remove item
        $removeAction = new RemoveFromCartAction($this->cartRepo);
        $cartDTO = $removeAction->execute($cartKey, $firstItemKey);
        $this->assertEquals(1, $cartDTO->itemCount);

        // Clear cart
        $clearAction = new ClearCartAction($this->cartRepo);
        $this->assertTrue($clearAction->execute($cartKey));

        $emptyCart = $getAction->execute($cartKey);
        $this->assertEquals(0, $emptyCart->itemCount);
    }
}