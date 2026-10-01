<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\ShippingCharge;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPosOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mondolshopbd.com',
        ]);
    }

    public function test_admin_can_search_products_by_name_and_code()
    {
        $product = Product::create([
            'name' => 'Smart Watch Pro',
            'slug' => 'smart-watch-pro',
            'product_code' => 'SWP-101',
            'pro_barcode' => '8901234567890',
            'new_price' => 2500,
            'old_price' => 3000,
            'purchase_price' => 1800,
            'stock' => 50,
            'status' => 1,
            'category_id' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.order.product_search', ['q' => 'SWP-101']));

        $response->assertStatus(200);
        $response->assertJson([
            'exact_match' => true,
        ]);
        $this->assertEquals('Smart Watch Pro', $response->json('matched_product.name'));
    }

    public function test_admin_can_place_pos_order_successfully()
    {
        $product = Product::create([
            'name' => 'Leather Wallet',
            'slug' => 'leather-wallet',
            'product_code' => 'LW-202',
            'new_price' => 800,
            'old_price' => 1000,
            'purchase_price' => 500,
            'stock' => 20,
            'status' => 1,
            'category_id' => 1,
        ]);

        $shipping = new ShippingCharge();
        $shipping->name = 'Inside Dhaka';
        $shipping->amount = 60;
        $shipping->status = 1;
        $shipping->save();

        // Add to POS cart
        CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 2,
            'price' => $product->new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => 'default.png',
                'old_price' => $product->old_price,
                'purchase_price' => $product->purchase_price,
                'product_discount' => 0,
            ],
        ]);

        $payload = [
            'name' => 'Rahim Khan',
            'phone' => '01811223344',
            'address' => 'House 12, Road 5, Mirpur 10, Dhaka',
            'area' => $shipping->id,
            'note' => 'Deliver in morning',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.order.store'), $payload);

        $response->assertRedirect('admin/order/pending');

        // Verify order in database
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatusEnum::Pending->value, $order->order_status);
        $this->assertEquals(1660, (float) $order->amount); // (800 * 2) + 60
        $this->assertEquals(60, (float) $order->shipping_charge);

        // Verify customer created
        $customer = Customer::where('phone', '01811223344')->first();
        $this->assertNotNull($customer);
        $this->assertEquals($customer->id, $order->customer_id);

        // Verify order details and stock decremented
        $orderDetails = OrderDetails::where('order_id', $order->id)->first();
        $this->assertNotNull($orderDetails);
        $this->assertEquals(2, $orderDetails->qty);

        $updatedProduct = Product::find($product->id);
        $this->assertEquals(18, $updatedProduct->stock); // 20 - 2
    }
}
