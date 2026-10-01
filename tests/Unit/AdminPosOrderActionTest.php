<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Order\Application\Actions\CreateAdminPosOrderAction;
use Modules\Order\Application\DTOs\AdminPosOrderInputDTO;
use Modules\Order\Domain\Events\OrderPlacedEvent;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Exceptions\DomainException;
use Tests\TestCase;

class AdminPosOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateAdminPosOrderAction $posAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->posAction = $this->app->make(CreateAdminPosOrderAction::class);
    }

    public function test_admin_pos_order_creation_with_auto_customer_registration(): void
    {
        Event::fake([OrderPlacedEvent::class]);

        $product1 = Product::create([
            'name' => 'Leather Wallet',
            'slug' => 'leather-wallet',
            'product_code' => 'WAL-001',
            'category_id' => 1,
            'purchase_price' => 500,
            'old_price' => 1200,
            'new_price' => 1000,
            'stock' => 15,
            'status' => 1,
        ]);

        $product2 = Product::create([
            'name' => 'Leather Belt',
            'slug' => 'leather-belt',
            'product_code' => 'BLT-001',
            'category_id' => 1,
            'purchase_price' => 400,
            'old_price' => 900,
            'new_price' => 800,
            'stock' => 10,
            'status' => 1,
        ]);

        $input = new AdminPosOrderInputDTO(
            items: [
                [
                    'productId' => $product1->id,
                    'productName' => $product1->name,
                    'unitPrice' => 1000.0,
                    'quantity' => 2,
                    'size' => 'Standard',
                    'color' => 'Brown',
                ],
                [
                    'productId' => $product2->id,
                    'productName' => $product2->name,
                    'unitPrice' => 800.0,
                    'quantity' => 1,
                    'size' => '34',
                    'color' => 'Black',
                ],
            ],
            customerName: 'Walk-in Customer',
            phone: '01799887766',
            address: 'Shop Counter',
            area: 1,
            shippingCost: 0.0,
            discount: 200.0,
            note: 'Counter POS sale',
            paymentMethod: PaymentMethodEnum::COD,
            orderStatus: OrderStatusEnum::PENDING
        );

        $orderDTO = $this->posAction->execute($input);

        // Assertions
        $this->assertNotNull($orderDTO->id);
        $this->assertStringStartsWith('POS-', $orderDTO->invoiceId);
        // Subtotal = (1000 * 2) + (800 * 1) = 2800 - 200 discount = 2600
        $this->assertEquals(2600.0, $orderDTO->amount);
        $this->assertEquals(200.0, $orderDTO->discount);
        $this->assertCount(2, $orderDTO->items);

        // Stock checks: product1: 15 - 2 = 13, product2: 10 - 1 = 9
        $product1->refresh();
        $this->assertEquals(13, $product1->stock);
        $product2->refresh();
        $this->assertEquals(9, $product2->stock);

        // Auto customer registered
        $customer = Customer::where('phone', '01799887766')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Walk-in Customer', $customer->name);

        Event::assertDispatched(OrderPlacedEvent::class, function ($event) use ($orderDTO) {
            return $event->orderId === $orderDTO->id && $event->amount->getAmount() === 2600.0;
        });
    }

    public function test_pos_order_fails_on_empty_items(): void
    {
        $input = new AdminPosOrderInputDTO(
            items: [],
            customerName: 'Walk-in Customer',
            phone: '01799887766',
            address: 'Counter'
        );

        $this->expectException(DomainException::class);
        $this->posAction->execute($input);
    }
}