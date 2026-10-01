<?php

namespace Tests\Unit;

use Modules\Order\Application\Actions\CalculateOrderPriceAction;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Entities\CartEntity;
use Modules\Order\Domain\Entities\CartItemEntity;
use Modules\Order\Domain\Services\PricingEngine;
use Shared\Domain\ValueObjects\Discount;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\Quantity;
use Tests\TestCase;

class PricingEngineTest extends TestCase
{
    private PricingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new PricingEngine();
    }

    public function test_pricing_engine_calculates_subtotal_correctly(): void
    {
        $cart = new CartEntity('cart_test_1');
        $cart->addItem(new CartItemEntity(1, 'Product A', Money::from(200), Quantity::from(2)));
        $cart->addItem(new CartItemEntity(2, 'Product B', Money::from(150), Quantity::from(1)));

        $breakdown = $this->engine->calculate($cart);

        $this->assertEquals(550.0, $breakdown->subtotal);
        $this->assertEquals(0.0, $breakdown->discountAmount);
        $this->assertEquals(0.0, $breakdown->shippingCharge);
        $this->assertEquals(550.0, $breakdown->grandTotal);
        $this->assertEquals(2, $breakdown->itemCount);
        $this->assertEquals(3, $breakdown->totalQuantity);
    }

    public function test_pricing_engine_with_percentage_discount(): void
    {
        $cart = new CartEntity('cart_test_2');
        $cart->addItem(new CartItemEntity(1, 'Product A', Money::from(1000), Quantity::from(1)));

        $discount = Discount::fromPercentage(15); // 15% off 1000 = 150
        $shipping = Money::from(60); // 60 BDT shipping

        $breakdown = $this->engine->calculate($cart, $discount, $shipping);

        $this->assertEquals(1000.0, $breakdown->subtotal);
        $this->assertEquals(150.0, $breakdown->discountAmount);
        $this->assertEquals(60.0, $breakdown->shippingCharge);
        $this->assertEquals(910.0, $breakdown->grandTotal); // 1000 - 150 + 60 = 910
    }

    public function test_pricing_engine_with_fixed_discount_exceeding_subtotal(): void
    {
        $cart = new CartEntity('cart_test_3');
        $cart->addItem(new CartItemEntity(1, 'Product A', Money::from(300), Quantity::from(1)));

        $discount = Discount::fromFixed(Money::from(500)); // Fixed 500 off 300
        $shipping = Money::from(120);

        $breakdown = $this->engine->calculate($cart, $discount, $shipping);

        $this->assertEquals(300.0, $breakdown->subtotal);
        $this->assertEquals(300.0, $breakdown->discountAmount); // Capped at subtotal
        $this->assertEquals(120.0, $breakdown->grandTotal); // Only shipping payable
    }

    public function test_calculate_order_price_action(): void
    {
        $cartRepo = $this->app->make(CartRepositoryInterface::class);
        $cart = new CartEntity('cart_action_key_1');
        $cart->addItem(new CartItemEntity(10, 'Winter Jacket', Money::from(2500), Quantity::from(1)));
        $cartRepo->save($cart);

        $action = new CalculateOrderPriceAction($cartRepo, $this->engine);
        $breakdown = $action->execute('cart_action_key_1', 'percentage', 10.0, 100.0);

        $this->assertEquals(2500.0, $breakdown->subtotal);
        $this->assertEquals(250.0, $breakdown->discountAmount);
        $this->assertEquals(100.0, $breakdown->shippingCharge);
        $this->assertEquals(2350.0, $breakdown->grandTotal);
    }
}