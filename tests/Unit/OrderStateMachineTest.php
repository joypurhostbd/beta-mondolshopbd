<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Order\Application\Actions\CancelOrderAction;
use Modules\Order\Application\Actions\Cart\AddToCartAction;
use Modules\Order\Application\Actions\ChangeOrderStatusAction;
use Modules\Order\Application\Actions\PlaceOrderAction;
use Modules\Order\Application\DTOs\PlaceOrderInputDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Events\OrderStatusChangedEvent;
use Modules\Order\Domain\Services\OrderStateMachine;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Exceptions\InvalidStateTransitionException;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private OrderStateMachine $stateMachine;
    private ChangeOrderStatusAction $changeStatusAction;
    private CancelOrderAction $cancelOrderAction;
    private PlaceOrderAction $placeOrderAction;
    private CartRepositoryInterface $cartRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new OrderStateMachine();
        $this->changeStatusAction = $this->app->make(ChangeOrderStatusAction::class);
        $this->cancelOrderAction = $this->app->make(CancelOrderAction::class);
        $this->placeOrderAction = $this->app->make(PlaceOrderAction::class);
        $this->cartRepo = $this->app->make(CartRepositoryInterface::class);
    }

    public function test_state_machine_valid_transitions(): void
    {
        $this->assertTrue($this->stateMachine->canTransition(OrderStatusEnum::PENDING, OrderStatusEnum::PROCESSING));
        $this->assertTrue($this->stateMachine->canTransition(OrderStatusEnum::PENDING, OrderStatusEnum::CANCELLED));
        $this->assertTrue($this->stateMachine->canTransition(OrderStatusEnum::PROCESSING, OrderStatusEnum::COMPLETED));
        $this->assertTrue($this->stateMachine->canTransition(OrderStatusEnum::COMPLETED, OrderStatusEnum::DELIVERED));
        $this->assertTrue($this->stateMachine->canTransition(OrderStatusEnum::DELIVERED, OrderStatusEnum::RETURNED));
    }

    public function test_state_machine_invalid_transitions(): void
    {
        // Cannot jump from PENDING directly to DELIVERED
        $this->assertFalse($this->stateMachine->canTransition(OrderStatusEnum::PENDING, OrderStatusEnum::DELIVERED));

        // Cannot change terminal states
        $this->assertFalse($this->stateMachine->canTransition(OrderStatusEnum::CANCELLED, OrderStatusEnum::PROCESSING));
        $this->assertFalse($this->stateMachine->canTransition(OrderStatusEnum::RETURNED, OrderStatusEnum::COMPLETED));
    }

    public function test_change_order_status_lifecycle_and_events(): void
    {
        Event::fake([OrderStatusChangedEvent::class]);

        $product = Product::create([
            'name' => 'Cotton Panjabi',
            'slug' => 'cotton-panjabi',
            'product_code' => 'PAN-001',
            'category_id' => 1,
            'purchase_price' => 800,
            'old_price' => 1800,
            'new_price' => 1500,
            'stock' => 10,
            'status' => 1,
        ]);

        $cartKey = 'state_machine_cart_1';
        $addToCartAction = new AddToCartAction($this->cartRepo);
        $addToCartAction->execute($cartKey, $product->id, $product->name, 1500.0, 2);

        $orderDTO = $this->placeOrderAction->execute(new PlaceOrderInputDTO(
            cartKey: $cartKey,
            customerName: 'Sizar Babu',
            phone: '01711223344',
            address: 'Dhaka',
            area: 1,
            shippingCost: 60.0
        ));

        // Initial stock 10 - 2 = 8
        $product->refresh();
        $this->assertEquals(8, $product->stock);

        // Transition 1: PENDING -> PROCESSING
        $this->changeStatusAction->execute($orderDTO->id, OrderStatusEnum::PROCESSING, 'Payment confirmed');

        Event::assertDispatched(OrderStatusChangedEvent::class, function ($event) use ($orderDTO) {
            return $event->orderId === $orderDTO->id && $event->newStatus === OrderStatusEnum::PROCESSING;
        });

        // Transition 2: PROCESSING -> CANCELLED (should restore stock 8 + 2 = 10)
        $this->cancelOrderAction->execute($orderDTO->id, 'Customer changed mind');

        $product->refresh();
        $this->assertEquals(10, $product->stock);
    }

    public function test_invalid_state_transition_throws_exception(): void
    {
        $product = Product::create([
            'name' => 'Silk Saree',
            'slug' => 'silk-saree',
            'product_code' => 'SAR-001',
            'category_id' => 1,
            'purchase_price' => 2000,
            'old_price' => 4500,
            'new_price' => 3800,
            'stock' => 5,
            'status' => 1,
        ]);

        $cartKey = 'state_machine_cart_2';
        $addToCartAction = new AddToCartAction($this->cartRepo);
        $addToCartAction->execute($cartKey, $product->id, $product->name, 3800.0, 1);

        $orderDTO = $this->placeOrderAction->execute(new PlaceOrderInputDTO(
            cartKey: $cartKey,
            customerName: 'Rahim',
            phone: '01811223344',
            address: 'Sylhet',
            area: 2
        ));

        $this->expectException(InvalidStateTransitionException::class);

        // Attempt invalid jump from PENDING directly to DELIVERED
        $this->changeStatusAction->execute($orderDTO->id, OrderStatusEnum::DELIVERED);
    }
}