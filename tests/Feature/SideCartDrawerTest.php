<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SideCartDrawerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cart::instance('shopping')->destroy();
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'Men Fashion',
            'slug' => 'men-fashion-' . rand(100, 999),
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => $attributes['name'] ?? 'Premium Sweatshirt White',
            'slug' => $attributes['slug'] ?? 'premium-sweatshirt-white-' . rand(100, 999),
            'product_code' => $attributes['product_code'] ?? 'P-' . rand(1000, 9999),
            'purchase_price' => $attributes['purchase_price'] ?? 400,
            'old_price' => $attributes['old_price'] ?? 999,
            'new_price' => $attributes['new_price'] ?? 699,
            'stock' => $attributes['stock'] ?? 30,
            'status' => 1,
            'category_id' => $category->id,
        ]);

        Productimage::create([
            'product_id' => $product->id,
            'image' => 'uploads/product/test-sweatshirt.jpg',
        ]);

        return $product;
    }

    public function test_ajax_add_to_cart_returns_json_with_rendered_side_cart_html_and_counts(): void
    {
        $product = $this->createProduct(['name' => 'Cozy Hoodie', 'new_price' => 850]);

        $response = $this->postJson(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
            'product_size' => 'XL',
            'product_color' => 'White',
        ], [
            'X-Side-Cart' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'cart_count',
            'subtotal',
            'side_cart_html',
        ]);

        $data = $response->json();
        $this->assertEquals('success', $data['status']);
        $this->assertEquals(1, $data['cart_count']);
        $this->assertEquals('850', $data['subtotal']);
        $this->assertStringContainsString('Cozy Hoodie', $data['side_cart_html']);
        $this->assertStringContainsString('XL', $data['side_cart_html']);
        $this->assertStringContainsString('White', $data['side_cart_html']);
        $this->assertStringContainsString('side-cart-stepper', $data['side_cart_html']);
    }

    public function test_side_cart_content_endpoint_returns_json_with_cart_items(): void
    {
        $product = $this->createProduct(['name' => 'Classic Polo', 'new_price' => 550]);

        Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 2,
            'price' => $product->new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => 'uploads/product/test.jpg',
                'product_size' => 'L',
                'product_color' => 'Navy',
            ],
        ]);

        $response = $this->getJson(route('cart.side_cart_content'), [
            'X-Side-Cart' => '1',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'cart_count' => 2,
            'subtotal' => '1100',
        ]);

        $this->assertStringContainsString('Classic Polo', $response->json('side_cart_html'));
        $this->assertStringContainsString('Navy', $response->json('side_cart_html'));
    }

    public function test_side_cart_increment_decrement_and_removal_via_ajax(): void
    {
        $product = $this->createProduct(['name' => 'Cotton Trouser', 'new_price' => 600]);

        $item = Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => $product->new_price,
            'options' => [
                'slug' => $product->slug,
                'image' => 'uploads/product/test.jpg',
            ],
        ]);

        $rowId = $item->rowId;

        // 1. Increment
        $incResponse = $this->getJson(route('cart.increment', [
            'id' => $rowId,
            'source' => 'side_cart',
        ]), ['X-Side-Cart' => '1']);

        $incResponse->assertStatus(200);
        $incResponse->assertJson([
            'status' => 'success',
            'cart_count' => 2,
            'subtotal' => '1200',
        ]);

        // 2. Decrement
        $decResponse = $this->getJson(route('cart.decrement', [
            'id' => $rowId,
            'source' => 'side_cart',
        ]), ['X-Side-Cart' => '1']);

        $decResponse->assertStatus(200);
        $decResponse->assertJson([
            'status' => 'success',
            'cart_count' => 1,
            'subtotal' => '600',
        ]);

        // 3. Remove
        $removeResponse = $this->getJson(route('cart.remove', [
            'id' => $rowId,
            'source' => 'side_cart',
        ]), ['X-Side-Cart' => '1']);

        $removeResponse->assertStatus(200);
        $removeResponse->assertJson([
            'status' => 'success',
            'cart_count' => 0,
            'subtotal' => '0',
        ]);
        $this->assertStringContainsString('আপনার কার্ট বর্তমানে খালি', $removeResponse->json('side_cart_html'));
    }

    public function test_side_cart_renders_empty_state_when_cart_is_empty(): void
    {
        $response = $this->getJson(route('cart.side_cart_content'));
        $response->assertStatus(200);
        $this->assertStringContainsString('আপনার কার্ট বর্তমানে খালি', $response->json('side_cart_html'));
        $this->assertStringContainsString('কেনাকাটা শুরু করুন', $response->json('side_cart_html'));
    }

    public function test_get_add_to_cart_route_returns_side_cart_payload(): void
    {
        $product = $this->createProduct(['name' => 'Drop Shoulder T-Shirt', 'new_price' => 450]);

        $response = $this->getJson('/add-to-cart/' . $product->id . '/1?color=Black&size=M');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'cart_count' => 1,
            'subtotal' => '450',
        ]);
        $this->assertStringContainsString('Drop Shoulder T-Shirt', $response->json('side_cart_html'));
    }

    public function test_product_page_renders_side_cart_drawer_and_ajax_button(): void
    {
        $product = $this->createProduct(['name' => 'Winter Sweatshirt', 'new_price' => 699]);

        $setting = GeneralSetting::create([
            'name' => 'Mondol Store',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
        ]);

        $contact = Contact::create([
            'phone' => '01972101994',
            'email' => 'sales@mondolshopbd.com',
            'address' => 'Dhaka',
            'hotline' => '01972101994',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        $response = $this->get(route('product', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('id="side-cart"', false);
        $response->assertSee('id="side-cart-overlay"', false);
        $response->assertSee('id="close-side-cart"', false);
        $response->assertSee('id="details-add-to-cart-btn"', false);
        $response->assertSee('id="side-cart-footer"', false);
        $response->assertSee('অর্ডার সম্পন্ন করুন', false);
        $response->assertSee('side-cart-continue-btn', false);
        $response->assertSee('আরো কেনাকাটা করুন', false);
    }
}