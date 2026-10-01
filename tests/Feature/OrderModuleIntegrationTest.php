<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Order\Application\Actions\CancelOrderAction;
use Modules\Order\Application\Actions\Cart\AddToCartAction;
use Modules\Order\Application\Actions\Cart\GetCartAction;
use Modules\Order\Application\Actions\ChangeOrderStatusAction;
use Modules\Order\Application\Actions\CreateAdminPosOrderAction;
use Modules\Order\Application\Actions\PlaceOrderAction;
use Modules\Order\Application\DTOs\AdminPosOrderInputDTO;
use Modules\Order\Application\DTOs\PlaceOrderInputDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Events\OrderPlacedEvent;
use Modules\Order\Domain\Events\OrderStatusChangedEvent;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Contracts\Modules\OrderModuleInterface;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Exceptions\InsufficientStockException;
use Tests\TestCase;

class OrderModuleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private CartRepositoryInterface $cartRepo;
    private PlaceOrderAction $placeOrderAction;
    private ChangeOrderStatusAction $changeStatusAction;
    private CancelOrderAction $cancelOrderAction;
    private CreateAdminPosOrderAction $posAction;
    private OrderModuleInterface $orderModuleService;
    private InventoryModuleInterface $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartRepo = $this->app->make(CartRepositoryInterface::class);
        $this->placeOrderAction = $this->app->make(PlaceOrderAction::class);
        $this->changeStatusAction = $this->app->make(ChangeOrderStatusAction::class);
        $this->cancelOrderAction = $this->app->make(CancelOrderAction::class);
        $this->posAction = $this->app->make(CreateAdminPosOrderAction::class);
        $this->orderModuleService = $this->app->make(OrderModuleInterface::class);
        $this->inventoryService = $this->app->make(InventoryModuleInterface::class);
    }

    public function test_full_order_and_inventory_lifecycle_end_to_end(): void
    {
        Event::fake([OrderPlacedEvent::class, OrderStatusChangedEvent::class]);

        // 1. Arrange Catalog Products
        $product = Product::create([
            'name' => 'Premium Kurti',
            'slug' => 'premium-kurti',
            'product_code' => 'KUR-001',
            'category_id' => 1,
            'purchase_price' => 600,
            'old_price' => 1500,
            'new_price' => 1200,
            'stock' => 20,
            'status' => 1,
        ]);

        $cartKey = 'e2e_cart_session_101';
        $addToCartAction = new AddToCartAction($this->cartRepo);
        $getCartAction = new GetCartAction($this->cartRepo);

        // 2. Add to Redis Cart
        $addToCartAction->execute($cartKey, $product->id, $product->name, 1200.0, 3, 'L', 'Blue');
        $cart = $getCartAction->execute($cartKey);
        $this->assertEquals(3600.0, $cart->getTotal());
        $this->assertEquals(3, $cart->getTotalQuantity());

        // 3. Place Order (Checkout Orchestration)
        $orderDTO = $this->placeOrderAction->execute(new PlaceOrderInputDTO(
            cartKey: $cartKey,
            customerName: 'Tanvir Hossain',
            phone: '01899887766',
            address: 'Banani, Dhaka',
            area: 1,
            shippingCost: 80.0
        ));

        // Total = 3600 + 80 = 3680
        $this->assertNotNull($orderDTO->id);
        $this->assertEquals(3680.0, $orderDTO->amount);
        $this->assertCount(1, $orderDTO->items);

        // Stock reserved: 20 - 3 = 17
        $product->refresh();
        $this->assertEquals(17, $product->stock);

        // Cart cleared
        $clearedCart = $getCartAction->execute($cartKey);
        $this->assertTrue($clearedCart->isEmpty());

        // Order event dispatched
        Event::assertDispatched(OrderPlacedEvent::class);

        // 4. Order Module Interface Verification
        $orderData = $this->orderModuleService->findOrderById($orderDTO->id);
        $this->assertNotNull($orderData);
        $this->assertEquals($orderDTO->invoiceId, $orderData['invoice_id']);

        // 5. State Machine Lifecycle Transitions: PENDING -> PROCESSING -> COMPLETED -> DELIVERED
        $this->orderModuleService->updateOrderStatus($orderDTO->id, OrderStatusEnum::PROCESSING);
        $orderData = $this->orderModuleService->findOrderById($orderDTO->id);
        $this->assertEquals(OrderStatusEnum::PROCESSING->value, $orderData['order_status']);

        $this->changeStatusAction->execute($orderDTO->id, OrderStatusEnum::COMPLETED, 'Shipped to courier');
        $this->changeStatusAction->execute($orderDTO->id, OrderStatusEnum::DELIVERED, 'Customer received parcel');

        $orderData = $this->orderModuleService->findOrderById($orderDTO->id);
        $this->assertEquals(OrderStatusEnum::DELIVERED->value, $orderData['order_status']);
    }

    public function test_order_cancellation_releases_inventory_stock(): void
    {
        Event::fake([OrderPlacedEvent::class, OrderStatusChangedEvent::class]);

        $product = Product::create([
            'name' => 'Winter Hoodie',
            'slug' => 'winter-hoodie',
            'product_code' => 'HOD-001',
            'category_id' => 1,
            'purchase_price' => 900,
            'old_price' => 2000,
            'new_price' => 1600,
            'stock' => 10,
            'status' => 1,
        ]);

        $cartKey = 'e2e_cart_session_102';
        $addToCartAction = new AddToCartAction($this->cartRepo);
        $addToCartAction->execute($cartKey, $product->id, $product->name, 1600.0, 4);

        $orderDTO = $this->placeOrderAction->execute(new PlaceOrderInputDTO(
            cartKey: $cartKey,
            customerName: 'Anik Rahman',
            phone: '01755443322',
            address: 'Mirpur, Dhaka',
            area: 1,
            shippingCost: 60.0
        ));

        // Stock reserved: 10 - 4 = 6
        $product->refresh();
        $this->assertEquals(6, $product->stock);

        // Cancel order -> Stock must be restored: 6 + 4 = 10
        $this->cancelOrderAction->execute($orderDTO->id, 'Customer changed decision');

        $product->refresh();
        $this->assertEquals(10, $product->stock);

        Event::assertDispatched(OrderStatusChangedEvent::class, function ($event) use ($orderDTO) {
            return $event->orderId === $orderDTO->id && $event->newStatus === OrderStatusEnum::CANCELLED;
        });
    }

    public function test_admin_pos_order_full_orchestration(): void
    {
        Event::fake([OrderPlacedEvent::class]);

        $product = Product::create([
            'name' => 'Denim Jacket',
            'slug' => 'denim-jacket',
            'product_code' => 'DNM-001',
            'category_id' => 1,
            'purchase_price' => 1200,
            'old_price' => 2800,
            'new_price' => 2400,
            'stock' => 8,
            'status' => 1,
        ]);

        $input = new AdminPosOrderInputDTO(
            items: [
                [
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'unitPrice' => 2400.0,
                    'quantity' => 2,
                    'size' => 'XL',
                    'color' => 'Dark Blue',
                ]
            ],
            customerName: 'POS Walkin Client',
            phone: '01911998877',
            address: 'Showroom Counter',
            area: 1,
            shippingCost: 0.0,
            discount: 300.0,
            note: 'Walk-in cash sale'
        );

        $orderDTO = $this->posAction->execute($input);

        // 2400 * 2 = 4800 - 300 = 4500
        $this->assertEquals(4500.0, $orderDTO->amount);
        $this->assertEquals(300.0, $orderDTO->discount);

        // Stock: 8 - 2 = 6
        $product->refresh();
        $this->assertEquals(6, $product->stock);

        // Customer auto-registered
        $customer = Customer::where('phone', '01911998877')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('POS Walkin Client', $customer->name);
    }

    public function test_inventory_insufficient_stock_exception_blocks_checkout(): void
    {
        $product = Product::create([
            'name' => 'Limited Watch',
            'slug' => 'limited-watch',
            'product_code' => 'WCH-001',
            'category_id' => 1,
            'purchase_price' => 3000,
            'old_price' => 6000,
            'new_price' => 5000,
            'stock' => 2,
            'status' => 1,
        ]);

        $cartKey = 'e2e_cart_session_103';
        $addToCartAction = new AddToCartAction($this->cartRepo);
        // Attempting to order 5 items when only 2 available
        $addToCartAction->execute($cartKey, $product->id, $product->name, 5000.0, 5);

        $this->expectException(InsufficientStockException::class);

        $this->placeOrderAction->execute(new PlaceOrderInputDTO(
            cartKey: $cartKey,
            customerName: 'Imran',
            phone: '01611223344',
            address: 'Uttara, Dhaka',
            area: 1
        ));
    }
}