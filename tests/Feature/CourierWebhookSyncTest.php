<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierWebhookSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_steadfast_webhook_syncs_delivered_status()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'slug' => 'john-doe-1',
            'phone' => '01711223344',
            'password' => bcrypt('password'),
            'status' => 'active',
            'verify' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 99101,
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Shipped->value,
            'consignment_id' => 'STF-889977',
        ]);

        $payload = [
            'tracking_code' => 'STF-889977',
            'status' => 'delivered',
            'invoice_id' => $order->invoice_id,
        ];

        $response = $this->postJson(route('api.courier.webhook', ['provider' => 'steadfast']), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'result' => [
                'status' => 'success',
                'new_status' => OrderStatusEnum::Delivered->value,
            ],
        ]);

        $this->assertEquals(OrderStatusEnum::Delivered->value, $order->fresh()->order_status);
    }

    public function test_pathao_webhook_syncs_cancelled_status_and_releases_inventory()
    {
        $product = Product::create([
            'name' => 'Cotton Shirt',
            'slug' => 'cotton-shirt',
            'product_code' => 'CS-101',
            'new_price' => 700,
            'old_price' => 900,
            'purchase_price' => 400,
            'stock' => 10,
            'status' => 1,
            'category_id' => 1,
        ]);

        $customer = Customer::create([
            'name' => 'Jane Doe',
            'slug' => 'jane-doe-2',
            'phone' => '01911223344',
            'password' => bcrypt('password'),
            'status' => 'active',
            'verify' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 99102,
            'amount' => 760,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Shipped->value,
            'consignment_id' => 'PTH-667788',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 400,
            'product_discount' => 0,
            'sale_price' => 700,
            'qty' => 2,
        ]);

        $payload = [
            'consignment_id' => 'PTH-667788',
            'order_status' => 'cancelled',
            'merchant_order_id' => $order->invoice_id,
        ];

        $response = $this->postJson(route('api.courier.webhook', ['provider' => 'pathao']), $payload);

        $response->assertStatus(200);
        $this->assertEquals(OrderStatusEnum::Cancelled->value, $order->fresh()->order_status);

        // Product stock should have been restored by 2 (10 + 2 = 12)
        $this->assertEquals(12, $product->fresh()->stock);
    }
}
