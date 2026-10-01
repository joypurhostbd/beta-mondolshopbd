<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\ShippingCharge;
use App\Models\PaymentGateway;
use App\Models\IncompleteOrder;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cart::instance('shopping')->destroy();
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = new Category();
        $category->name = 'Test Category';
        $category->slug = 'test-category-' . rand(1000, 9999);
        $category->status = 1;
        $category->save();

        $prod = new Product();
        $prod->name = $attributes['name'] ?? 'Test Product';
        $prod->slug = $attributes['slug'] ?? 'test-product-' . rand(1000, 9999);
        $prod->category_id = $category->id;
        $prod->product_code = $attributes['product_code'] ?? 'P-' . rand(10000, 99999);
        $prod->purchase_price = $attributes['purchase_price'] ?? 500;
        $prod->old_price = $attributes['old_price'] ?? 800;
        $prod->new_price = $attributes['new_price'] ?? 700;
        $prod->stock = $attributes['stock'] ?? 50;
        $prod->status = $attributes['status'] ?? 1;
        $prod->save();

        $img = new Productimage();
        $img->product_id = $prod->id;
        $img->image = 'public/uploads/product/default.png';
        $img->save();

        return $prod;
    }

    protected function createShippingAndGateways(): void
    {
        $shipping = new ShippingCharge();
        $shipping->name = 'Inside Dhaka';
        $shipping->amount = 60;
        $shipping->status = 1;
        $shipping->save();

        $bkash = new PaymentGateway();
        $bkash->type = 'bkash';
        $bkash->status = 1;
        $bkash->save();

        $shurjopay = new PaymentGateway();
        $shurjopay->type = 'shurjopay';
        $shurjopay->status = 1;
        $shurjopay->save();
    }

    public function test_product_can_be_added_to_cart_via_post()
    {
        $product = $this->createProduct(['name' => 'Cotton T-Shirt', 'new_price' => 500]);

        $response = $this->from('/product/' . $product->slug)->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 2,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $response->assertRedirect('/product/' . $product->slug);
        $this->assertEquals(2, Cart::instance('shopping')->count());
    }

    public function test_product_can_be_added_to_cart_and_redirect_to_checkout()
    {
        $product = $this->createProduct(['name' => 'Winter Jacket', 'new_price' => 1500]);

        $response = $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'অর্ডার করুন',
        ]);

        $response->assertRedirect(route('customer.checkout'));
        $this->assertEquals(1, Cart::instance('shopping')->count());
    }

    public function test_cart_can_increment_and_decrement_item_quantity()
    {
        $product = $this->createProduct(['name' => 'Sneakers', 'new_price' => 1200]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $rowId = Cart::instance('shopping')->content()->first()->rowId;

        // Increment
        $responseInc = $this->get(route('cart.increment', ['id' => $rowId]));
        $responseInc->assertStatus(200);
        $this->assertEquals(2, Cart::instance('shopping')->count());

        // Decrement
        $responseDec = $this->get(route('cart.decrement', ['id' => $rowId]));
        $responseDec->assertStatus(200);
        $this->assertEquals(1, Cart::instance('shopping')->count());
    }

    public function test_cart_item_can_be_removed()
    {
        $product = $this->createProduct(['name' => 'Cap', 'new_price' => 200]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $rowId = Cart::instance('shopping')->content()->first()->rowId;

        $response = $this->get(route('cart.remove', ['id' => $rowId]));
        $response->assertStatus(200);
        $this->assertEquals(0, Cart::instance('shopping')->count());
    }

    public function test_cart_count_returns_view_with_total_items()
    {
        $product = $this->createProduct(['name' => 'Belt', 'new_price' => 350]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 3,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $response = $this->get(route('cart.count'));
        $response->assertStatus(200);
        $response->assertSee('3');
    }

    public function test_checkout_page_renders_with_shipping_and_gateways()
    {
        $this->createShippingAndGateways();
        $product = $this->createProduct(['name' => 'Formal Shoes', 'new_price' => 2500]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'অর্ডার করুন',
        ]);

        $response = $this->get(route('customer.checkout'));

        $response->assertStatus(200);
        $response->assertViewHas('shippingcharge');
        $response->assertSessionHas('shipping', 60);
    }

    public function test_incomplete_order_saves_lead_data_during_checkout()
    {
        $product = $this->createProduct(['name' => 'Polo Shirt', 'new_price' => 600]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $payload = [
            'name' => 'Lead Customer',
            'phone' => '01712345678',
            'address' => 'Mirpur, Dhaka',
        ];

        $response = $this->post(route('incomplete.order'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('incomplete_orders', [
            'name' => 'Lead Customer',
            'phone' => '01712345678',
            'address' => 'Mirpur, Dhaka',
        ]);
    }

    public function test_checkout_page_renders_delivery_area_radio_buttons(): void
    {
        $shippingInside = ShippingCharge::create([
            'name' => 'ঢাকার ভিতরে ৭০ টাকা',
            'amount' => 70,
            'status' => 1,
        ]);

        $shippingOutside = ShippingCharge::create([
            'name' => 'ঢাকার বাহিরে ১২০ টাকা',
            'amount' => 120,
            'status' => 1,
        ]);

        $product = $this->createProduct(['name' => 'Trouser', 'new_price' => 500]);
        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $response = $this->get(route('customer.checkout'));

        $response->assertStatus(200);
        $response->assertSee('delivery-area-group', false);
        $response->assertSee('delivery-area-card', false);
        $response->assertSee('type="radio"', false);
        $response->assertSee('name="area"', false);
        $response->assertSee('value="' . $shippingInside->id . '"', false);
        $response->assertSee('value="' . $shippingOutside->id . '"', false);
        $response->assertSee('ঢাকার ভিতরে ৭০ টাকা', false);
        $response->assertSee('ঢাকার বাহিরে ১২০ টাকা', false);
    }

    public function test_incomplete_order_normalizes_bengali_digits_and_saves_unicode_lead_data(): void
    {
        $product = $this->createProduct(['name' => 'Drop Shoulder', 'new_price' => 849]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $payload = [
            'name' => 'সিজার বাবু',
            'phone' => '০১৯৭২১০১৯৯৪',
            'address' => 'গাতনশহর',
        ];

        $response = $this->post(route('incomplete.order'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('incomplete_orders', [
            'name' => 'সিজার বাবু',
            'phone' => '01972101994',
            'address' => 'গাতনশহর',
        ]);
    }

    public function test_checkout_order_save_normalizes_bengali_phone_digits_and_completes_order(): void
    {
        $this->createShippingAndGateways();
        $product = $this->createProduct(['name' => 'Premium T-Shirt', 'new_price' => 849]);

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $shipping = ShippingCharge::first();

        $payload = [
            'name' => 'সিজার বাবু',
            'phone' => '০১৯৭২১০১৯৯৪',
            'address' => 'গাতনশহর',
            'area' => $shipping->id,
            'payment_method' => 'Cash On Delivery',
        ];

        $response = $this->post(route('customer.ordersave'), $payload);

        $this->assertDatabaseHas('customers', [
            'phone' => '01972101994',
        ]);

        $this->assertDatabaseHas('shippings', [
            'phone' => '01972101994',
            'name' => 'সিজার বাবু',
            'address' => 'গাতনশহর',
        ]);
    }
}
