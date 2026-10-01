<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\Shipping;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderPaymentCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cart::instance('shopping')->destroy();
        $this->createOrderStatus(1, 'Pending');
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = new Category();
        $category->name = 'Clothing';
        $category->slug = 'clothing-' . rand(1000, 9999);
        $category->status = 1;
        $category->save();

        $prod = new Product();
        $prod->name = $attributes['name'] ?? 'Test Shirt';
        $prod->slug = $attributes['slug'] ?? 'test-shirt-' . rand(1000, 9999);
        $prod->category_id = $category->id;
        $prod->product_code = $attributes['product_code'] ?? 'P-' . rand(10000, 99999);
        $prod->purchase_price = $attributes['purchase_price'] ?? 400;
        $prod->old_price = $attributes['old_price'] ?? 700;
        $prod->new_price = $attributes['new_price'] ?? 600;
        $prod->stock = $attributes['stock'] ?? 50;
        $prod->status = $attributes['status'] ?? 1;
        $prod->save();

        $img = new Productimage();
        $img->product_id = $prod->id;
        $img->image = 'public/uploads/product/default.png';
        $img->save();

        return $prod;
    }

    protected function createShippingCharge(): ShippingCharge
    {
        $shipping = new ShippingCharge();
        $shipping->name = 'Inside Dhaka';
        $shipping->amount = 60;
        $shipping->status = 1;
        $shipping->save();

        return $shipping;
    }

    protected function createCustomer(array $attributes = []): Customer
    {
        $customer = new Customer();
        $customer->name = $attributes['name'] ?? 'Active Customer';
        $customer->slug = Str::slug($customer->name) . '-' . rand(100, 999);
        $customer->phone = $attributes['phone'] ?? '01711223344';
        $customer->email = $attributes['email'] ?? ('customer' . rand(100, 999) . '@example.com');
        $customer->password = bcrypt('password123');
        $customer->verify = 1;
        $customer->status = 1;
        $customer->save();

        return $customer;
    }

    protected function createOrderStatus(int $id = 1, string $name = 'Pending'): OrderStatus
    {
        $status = new OrderStatus();
        $status->id = $id;
        $status->name = $name;
        $status->slug = Str::slug($name);
        $status->status = 1;
        $status->save();

        return $status;
    }

    public function test_guest_can_place_order_successfully_with_cod()
    {
        $product = $this->createProduct(['new_price' => 600, 'purchase_price' => 400]);
        $shippingCharge = $this->createShippingCharge();

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 2,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $payload = [
            'name' => 'John Doe',
            'phone' => '01811223344',
            'address' => 'House 10, Road 5, Dhanmondi',
            'area' => $shippingCharge->id,
            'payment_method' => 'cod',
            'note' => 'Deliver in afternoon',
        ];

        $response = $this->withSession(['shipping' => 60])
            ->post(route('customer.ordersave'), $payload);

        $this->assertDatabaseHas('orders', [
            'amount' => 1260,
            'shipping_charge' => 60,
            'discount' => 0,
            'order_status' => '1',
            'note' => 'Deliver in afternoon',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect('customer/order-success/' . $order->id);

        $this->assertDatabaseHas('customers', [
            'id' => $order->customer_id,
            'phone' => '01811223344',
            'name' => 'John Doe',
            'status' => 1,
            'verify' => 1,
        ]);

        $this->assertDatabaseHas('shippings', [
            'order_id' => $order->id,
            'name' => 'John Doe',
            'phone' => '01811223344',
            'area' => 'Inside Dhaka',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => 1260,
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'sale_price' => 600,
        ]);

        $this->assertEquals(0, Cart::instance('shopping')->count());
        $this->assertFalse(Auth::guard('customer')->check(), 'Guest checkout must not auto-login without password.');
    }

    public function test_guest_checkout_does_not_auto_login_existing_customer()
    {
        $existingCustomer = $this->createCustomer([
            'name' => 'Sizar Babu',
            'phone' => '01972101994',
        ]);

        $product = $this->createProduct(['new_price' => 750]);
        $shippingCharge = $this->createShippingCharge();

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
        ]);

        $payload = [
            'name' => 'MD Bakhtiyar Hossain',
            'phone' => '01972101994',
            'address' => 'Gatonshahar',
            'area' => $shippingCharge->id,
            'payment_method' => 'cod',
        ];

        $response = $this->post(route('customer.ordersave'), $payload);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals($existingCustomer->id, $order->customer_id);

        // Crucial security assertion: Guest user is NOT logged in as existing customer
        $this->assertFalse(Auth::guard('customer')->check());

        // Guest can still view their just-placed order confirmation
        $successResponse = $this->get(route('customer.order_success', $order->id));
        $successResponse->assertStatus(200);
        $successResponse->assertSee('MD Bakhtiyar Hossain');
    }

    public function test_authenticated_customer_order_links_to_their_account()
    {
        $customer = $this->createCustomer();
        Auth::guard('customer')->login($customer);

        $product = $this->createProduct(['new_price' => 800]);
        $shippingCharge = $this->createShippingCharge();

        $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 1,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        $payload = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => 'Customer Address',
            'area' => $shippingCharge->id,
            'payment_method' => 'cod',
        ];

        $response = $this->withSession(['shipping' => 60])
            ->post(route('customer.ordersave'), $payload);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals($customer->id, $order->customer_id);
    }

    public function test_order_save_fails_when_cart_is_empty()
    {
        $shippingCharge = $this->createShippingCharge();

        $payload = [
            'name' => 'Empty Cart Buyer',
            'phone' => '01700000000',
            'address' => 'Dhaka',
            'area' => $shippingCharge->id,
            'payment_method' => 'cod',
        ];

        $response = $this->from(route('customer.checkout'))
            ->post(route('customer.ordersave'), $payload);

        $response->assertRedirect(route('customer.checkout'));
        $this->assertEquals(0, Order::count());
    }

    public function test_order_success_page_renders_order_details()
    {
        $customer = $this->createCustomer();

        $order = new Order();
        $order->invoice_id = 'INV123';
        $order->amount = 1000;
        $order->discount = 0;
        $order->shipping_charge = 60;
        $order->customer_id = $customer->id;
        $order->order_status = 1;
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer->id;
        $shipping->name = 'Test Buyer';
        $shipping->phone = '01711223344';
        $shipping->address = 'Test Address';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->customer_id = $customer->id;
        $payment->payment_method = 'Cash on Delivery';
        $payment->amount = 1000;
        $payment->payment_status = 'pending';
        $payment->save();

        $response = $this->withSession(['last_order_id' => $order->id])->get(route('customer.order_success', $order->id));

        $response->assertStatus(200);
        $response->assertViewHas('order');
        $response->assertSee('আপনার অর্ডারটি');
        $response->assertSee('সফলভাবে গ্রহণ করা হয়েছে!');
        $response->assertSee('অর্ডার কনফার্মড');
        $response->assertSee('Your Order Details');
        $response->assertSee('Ordered Products');
        $response->assertSee('Order Summary');
        $response->assertSee('Delivery Address');
        $response->assertSee('Go To Home');
        $response->assertSee('পূর্ণাঙ্গ ইনভয়েস দেখুন বা প্রিন্ট করুন');

        // Test IDOR protection: unauthorized user cannot access random order
        $unauthorizedResponse = $this->withSession(['last_order_id' => null])->get(route('customer.order_success', $order->id));
        $unauthorizedResponse->assertStatus(302);
    }

    public function test_ajax_order_save_returns_success_status_and_redirect_url()
    {
        $product = $this->createProduct(['new_price' => 500]);
        Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 500,
            'options' => ['slug' => $product->slug, 'image' => 'default.jpg'],
        ]);

        $payload = [
            'name' => 'Ajax Buyer',
            'phone' => '01899112233',
            'address' => 'Mirpur, Dhaka',
            'area' => 'Inside Dhaka',
            'payment_method' => 'cod',
        ];

        $response = $this->withSession(['shipping' => 70])
            ->postJson(route('customer.ordersave'), $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'order_id',
            'invoice_id',
            'amount',
            'redirect_url',
        ]);
        $response->assertJson([
            'status' => 'success',
        ]);
        $this->assertStringContainsString('customer/order-success/', $response->json('redirect_url'));
    }

    public function test_guest_can_access_own_invoice_via_last_order_id()
    {
        $order = new Order();
        $order->invoice_id = 'INV-GUEST-001';
        $order->amount = 570;
        $order->discount = 0;
        $order->shipping_charge = 70;
        $order->customer_id = 999;
        $order->order_status = 1;
        $order->save();

        $response = $this->withSession(['last_order_id' => $order->id])
            ->get(route('customer.invoice', ['id' => $order->id]));

        $response->assertStatus(200);
        $response->assertSee('INV-GUEST-001');
    }

    public function test_authenticated_customer_can_view_order_history()
    {
        $customer = $this->createCustomer();
        Auth::guard('customer')->login($customer);

        $order = new Order();
        $order->invoice_id = 'INV456';
        $order->amount = 1500;
        $order->discount = 0;
        $order->shipping_charge = 60;
        $order->customer_id = $customer->id;
        $order->order_status = 1;
        $order->save();

        $response = $this->get(route('customer.orders'));

        $response->assertStatus(200);
        $response->assertViewHas('orders');
    }

    public function test_order_tracking_finds_order_by_phone_and_invoice()
    {
        $customer = $this->createCustomer();

        $order = new Order();
        $order->invoice_id = '12345';
        $order->amount = 1200;
        $order->discount = 0;
        $order->shipping_charge = 60;
        $order->customer_id = $customer->id;
        $order->order_status = 1;
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer->id;
        $shipping->name = 'Track Buyer';
        $shipping->phone = '01799887766';
        $shipping->address = 'Banani, Dhaka';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        $response = $this->get(route('customer.order_track_result', [
            'phone' => '01799887766',
            'invoice_id' => $order->invoice_id,
        ]));

        $response->assertStatus(200);
    }
}
