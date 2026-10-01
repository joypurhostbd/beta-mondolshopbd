<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Order\Application\Actions\Cart\AddToCartAction;
use Modules\Order\Application\Actions\PlaceOrderAction;
use Modules\Order\Application\DTOs\PlaceOrderInputDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Modules\Order\Domain\Events\OrderPlacedEvent;
use Modules\Order\Domain\Services\PricingEngine;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Contracts\Modules\OrderModuleInterface;
use Shared\Domain\Exceptions\DomainException;
use Tests\TestCase;

class PlaceOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CartRepositoryInterface $cartRepo;
    private OrderRepositoryInterface $orderRepo;
    private PlaceOrderAction $placeOrderAction;
    private OrderModuleInterface $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartRepo = $this->app->make(CartRepositoryInterface::class);
        $this->orderRepo = $this->app->make(OrderRepositoryInterface::class);
        $this->placeOrderAction = $this->app->make(PlaceOrderAction::class);
        $this->orderService = $this->app->make(OrderModuleInterface::class);
    }

    public function test_place_order_action_full_orchestration(): void
    {
        Event::fake([OrderPlacedEvent::class]);

        $product = Product::create([
            'name' => 'Leather Shoes',
            'slug' => 'leather-shoes',
            'product_code' => 'SHOE-001',
            'category_id' => 1,
            'purchase_price' => 1200,
            'old_price' => 2500,
            'new_price' => 2000,
            'stock' => 10,
            'status' => 1,
        ]);

        $cartKey = 'test_checkout_cart_1';
        $addToCartAction = new AddToCartAction($this->cartRepo);
        $addToCartAction->execute($cartKey, $product->id, $product->name, 2000.0, 2, '42', 'Black');

        $input = new PlaceOrderInputDTO(
            cartKey: $cartKey,
            customerName: 'Sizar Babu',
            phone: '01711122233',
            address: 'House 12, Road 4, Dhanmondi, Dhaka',
            area: 'Inside Dhaka',
            paymentMethod: 'Cash On Delivery',
            customerId: null,
            discountType: 'percentage',
            discountValue: 10.0, // 10% off 4000 = 400
            shippingCost: 60.0 // 60 BDT
        );

        $orderDTO = $this->placeOrderAction->execute($input);

        $this->assertNotNull($orderDTO->id);
        $this->assertNotEmpty($orderDTO->invoiceId);
        $this->assertEquals(3660.0, $orderDTO->amount); // 4000 - 400 + 60 = 3660
        $this->assertEquals('Sizar Babu', $orderDTO->customerName);
        $this->assertCount(1, $orderDTO->items);

        // Verify stock was deducted via inventory module
        $product->refresh();
        $this->assertEquals(8, $product->stock); // 10 - 2 = 8

        // Verify cart was cleared
        $this->assertTrue($this->cartRepo->get($cartKey)->isEmpty());

        // Verify event was dispatched
        Event::assertDispatched(OrderPlacedEvent::class, function ($event) use ($orderDTO) {
            return $event->orderId === $orderDTO->id && $event->amount->getAmount() === 3660.0;
        });

        // Verify order module interface findOrderById
        $foundOrder = $this->orderService->findOrderById($orderDTO->id);
        $this->assertNotNull($foundOrder);
        $this->assertEquals($orderDTO->invoiceId, $foundOrder['invoice_id']);
    }

    public function test_place_order_fails_on_empty_cart(): void
    {
        $this->expectException(DomainException::class);

        $input = new PlaceOrderInputDTO(
            cartKey: 'empty_cart_key',
            customerName: 'Jane Doe',
            phone: '01811223344',
            address: 'Chittagong'
        );

        $this->placeOrderAction->execute($input);
    }
}