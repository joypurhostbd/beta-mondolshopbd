<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ShippingCharge;
use App\Models\Size;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderEditAttributesTest extends TestCase
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

        OrderStatus::create(['name' => 'Pending', 'slug' => 'pending', 'status' => 1]);
    }

    public function test_admin_order_edit_populates_cart_with_product_size_and_color()
    {
        $product = Product::create([
            'name' => '2 Pieces Premium Drop Shoulder',
            'slug' => '2-pieces-premium-drop-shoulder',
            'product_code' => 'PDS-101',
            'new_price' => 849,
            'old_price' => 1000,
            'purchase_price' => 200,
            'stock' => 50,
            'status' => 1,
            'category_id' => 1,
        ]);

        $customer = Customer::create([
            'name' => 'Karim',
            'slug' => 'karim-101',
            'phone' => '01700000001',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '148607372',
            'amount' => 919,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $detail = OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 200,
            'sale_price' => 849,
            'product_discount' => 0,
            'product_size' => 'M',
            'product_color' => 'Navy',
            'qty' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);

        $cartItems = CartService::instance('pos_shopping')->content();
        $this->assertCount(1, $cartItems);

        $cartItem = $cartItems->first();
        $this->assertEquals('M', $cartItem->options->product_size);
        $this->assertEquals('Navy', $cartItem->options->product_color);
        $this->assertEquals($detail->id, $cartItem->options->details_id);
    }

    public function test_admin_can_fetch_product_attributes()
    {
        $product = Product::create([
            'name' => 'Casual Polo T-Shirt',
            'slug' => 'casual-polo-t-shirt',
            'product_code' => 'CPT-202',
            'new_price' => 650,
            'old_price' => 800,
            'purchase_price' => 300,
            'stock' => 25,
            'status' => 1,
            'category_id' => 1,
        ]);

        $sizeM = Size::create(['sizeName' => 'M', 'status' => 1]);
        $sizeL = Size::create(['sizeName' => 'L', 'status' => 1]);
        $product->sizes()->attach([$sizeM->id, $sizeL->id]);

        $colorBlack = Color::create(['colorName' => 'Black', 'color' => '#000000', 'status' => 1]);
        $product->colors()->attach([$colorBlack->id]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.order.product_attributes', ['id' => $product->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $product->id,
            'has_attributes' => true,
        ]);

        $sizes = collect($response->json('sizes'))->pluck('name')->toArray();
        $this->assertContains('M', $sizes);
        $this->assertContains('L', $sizes);

        $colors = collect($response->json('colors'))->pluck('name')->toArray();
        $this->assertContains('Black', $colors);
    }

    public function test_admin_can_add_product_to_cart_with_attributes()
    {
        $product = Product::create([
            'name' => 'Printed Hoodie',
            'slug' => 'printed-hoodie',
            'product_code' => 'PH-303',
            'new_price' => 1200,
            'old_price' => 1500,
            'purchase_price' => 500,
            'stock' => 30,
            'status' => 1,
            'category_id' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.order.cart_add', [
                'id' => $product->id,
                'product_size' => 'XL',
                'product_color' => 'Maroon',
                'qty' => 2,
            ]));

        $response->assertStatus(200);

        $cartItems = CartService::instance('pos_shopping')->content();
        $this->assertCount(1, $cartItems);

        $cartItem = $cartItems->first();
        $this->assertEquals(2, $cartItem->qty);
        $this->assertEquals('XL', $cartItem->options->product_size);
        $this->assertEquals('Maroon', $cartItem->options->product_color);
    }

    public function test_admin_can_update_cart_attribute_inline()
    {
        $product = Product::create([
            'name' => 'Basic Tee',
            'slug' => 'basic-tee',
            'product_code' => 'BT-404',
            'new_price' => 450,
            'old_price' => 500,
            'purchase_price' => 200,
            'stock' => 40,
            'status' => 1,
            'category_id' => 1,
        ]);

        $cartItem = CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => $product->new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => 'default.png',
                'old_price' => $product->old_price,
                'purchase_price' => $product->purchase_price,
                'product_discount' => 0,
                'product_size' => 'M',
                'product_color' => 'White',
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.order.cart_update_attribute', [
                'rowId' => $cartItem->rowId,
                'product_size' => 'XXL',
                'product_color' => 'Black',
            ]));

        $response->assertStatus(200);

        $updatedItem = CartService::instance('pos_shopping')->content()->where('rowId', $cartItem->rowId)->first();
        $this->assertNotNull($updatedItem);
        $this->assertEquals('XXL', $updatedItem->options->product_size);
        $this->assertEquals('Black', $updatedItem->options->product_color);
    }

    public function test_admin_order_update_persists_size_and_color()
    {
        $product = Product::create([
            'name' => 'Denim Jacket',
            'slug' => 'denim-jacket',
            'product_code' => 'DJ-505',
            'new_price' => 1800,
            'old_price' => 2200,
            'purchase_price' => 900,
            'stock' => 15,
            'status' => 1,
            'category_id' => 1,
        ]);

        $customer = Customer::create([
            'name' => 'Jamal Uddin',
            'slug' => 'jamal-uddin-505',
            'phone' => '01899999999',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '148607373',
            'amount' => 1870,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $detail = OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 900,
            'sale_price' => 1800,
            'product_discount' => 0,
            'product_size' => 'M',
            'product_color' => 'Navy',
            'qty' => 1,
        ]);

        $shipping = ShippingCharge::create([
            'name' => 'Inside Dhaka',
            'amount' => 70,
            'status' => 1,
        ]);

        // Load into edit
        $this->actingAs($this->admin)->get(route('admin.order.edit', $order->invoice_id));

        // Get rowId and update attribute to 'L' and 'Blue'
        $cartItem = CartService::instance('pos_shopping')->content()->first();
        $this->actingAs($this->admin)->getJson(route('admin.order.cart_update_attribute', [
            'rowId' => $cartItem->rowId,
            'product_size' => 'L',
            'product_color' => 'Blue',
        ]));

        // Submit update form
        $payload = [
            'order_id' => $order->id,
            'name' => 'Jamal Uddin Updated',
            'phone' => '01899999999',
            'address' => 'Mirpur DOHS, Dhaka',
            'area' => $shipping->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.order.update'), $payload);

        $response->assertRedirect();

        $detail = OrderDetails::where('order_id', $order->id)->first();
        $this->assertNotNull($detail);
        $this->assertEquals('L', $detail->product_size);
        $this->assertEquals('Blue', $detail->product_color);
    }

    public function test_admin_order_store_persists_size_and_color()
    {
        $product = Product::create([
            'name' => 'Track Pants',
            'slug' => 'track-pants',
            'product_code' => 'TP-606',
            'new_price' => 750,
            'old_price' => 900,
            'purchase_price' => 350,
            'stock' => 50,
            'status' => 1,
            'category_id' => 1,
        ]);

        $shipping = ShippingCharge::create([
            'name' => 'Outside Dhaka',
            'amount' => 130,
            'status' => 1,
        ]);

        // Add to POS cart with size and color
        CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => $product->new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => 'default.png',
                'old_price' => $product->old_price,
                'purchase_price' => $product->purchase_price,
                'product_discount' => 0,
                'product_size' => 'XL',
                'product_color' => 'Olive',
            ],
        ]);

        $payload = [
            'name' => 'Tanvir Hasan',
            'phone' => '01711223344',
            'address' => 'Rajshahi Sadar',
            'area' => $shipping->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.order.store'), $payload);

        $response->assertRedirect('admin/order/pending');

        $order = Order::first();
        $this->assertNotNull($order);

        $detail = OrderDetails::where('order_id', $order->id)->first();
        $this->assertNotNull($detail);
        $this->assertEquals('XL', $detail->product_size);
        $this->assertEquals('Olive', $detail->product_color);
    }

    public function test_product_discount_preserves_attributes_in_cart()
    {
        $product = Product::create([
            'name' => 'Formal Shirt',
            'slug' => 'formal-shirt',
            'product_code' => 'FS-707',
            'new_price' => 1100,
            'old_price' => 1300,
            'purchase_price' => 500,
            'stock' => 20,
            'status' => 1,
            'category_id' => 1,
        ]);

        $cartItem = CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => $product->new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => 'default.png',
                'old_price' => $product->old_price,
                'purchase_price' => $product->purchase_price,
                'product_discount' => 0,
                'product_size' => 'M',
                'product_color' => 'Sky Blue',
            ],
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.order.product_discount', [
                'id' => $cartItem->rowId,
                'discount' => 50,
            ]));

        $response->assertStatus(200);

        $updatedItem = CartService::instance('pos_shopping')->content()->where('rowId', $cartItem->rowId)->first();
        $this->assertNotNull($updatedItem);
        $this->assertEquals(50, $updatedItem->options->product_discount);
        $this->assertEquals('M', $updatedItem->options->product_size);
        $this->assertEquals('Sky Blue', $updatedItem->options->product_color);
    }
}
