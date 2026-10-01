<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Jobs\SendMetaCapiEventJob;
use App\Listeners\SendMetaPurchaseConversion;
use App\Models\Customer;
use App\Models\EcomPixel;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Shipping;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingCharge;
use App\Services\MetaConversionsApiService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MetaConversionsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'status' => 1,
        ]);

        $permissions = [
            'setting-list',
            'setting-create',
            'setting-edit',
            'setting-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->admin->givePermissionTo($permissions);
    }

    public function test_user_data_hashing_and_phone_normalization()
    {
        $service = new MetaConversionsApiService();

        $this->assertEquals('8801711111111', $service->normalizePhone('01711111111'));
        $this->assertEquals('8801711111111', $service->normalizePhone('8801711111111'));
        $this->assertEquals('8801711111111', $service->normalizePhone('+880 1711-111111'));

        $rawEmail = ' Test.User@Example.COM ';
        $hashedEmail = hash('sha256', 'test.user@example.com');
        $this->assertEquals($hashedEmail, $service->hashValue($rawEmail));

        $userData = $service->buildUserData([
            'email' => 'customer@gmail.com',
            'phone' => '01812345678',
            'name' => 'Rahim Ahmed',
            'city' => 'Dhaka',
        ]);

        $this->assertEquals([hash('sha256', 'customer@gmail.com')], $userData['em']);
        $this->assertEquals([hash('sha256', '8801812345678')], $userData['ph']);
        $this->assertEquals([hash('sha256', 'rahim')], $userData['fn']);
        $this->assertEquals([hash('sha256', 'ahmed')], $userData['ln']);
        $this->assertEquals([hash('sha256', 'dhaka')], $userData['ct']);
    }

    public function test_capi_send_event_dispatches_http_request_with_correct_payload()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id' => 'abc123xyz',
            ], 200),
        ]);

        $pixel = EcomPixel::create([
            'code' => '123456789012345',
            'access_token' => 'EAABtestToken12345',
            'test_event_code' => 'TEST99999',
            'status' => 1,
            'capi_status' => 1,
        ]);

        $service = new MetaConversionsApiService();
        $result = $service->sendEvent(
            'Purchase',
            ['email' => 'buyer@example.com', 'phone' => '01711111111'],
            ['currency' => 'BDT', 'value' => 1500],
            'order_1001',
            'https://mondolshopbd.test/order-success/1'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('order_1001', $result['event_id']);

        Http::assertSent(function ($request) {
            $data = $request['data'][0] ?? [];
            return str_contains($request->url(), '123456789012345/events')
                && $request['access_token'] === 'EAABtestToken12345'
                && $data['event_name'] === 'Purchase'
                && $data['event_id'] === 'order_1001'
                && $data['action_source'] === 'website'
                && ($request['test_event_code'] ?? null) === 'TEST99999'
                && ($data['custom_data']['value'] ?? 0) == 1500;
        });
    }

    public function test_order_placed_event_dispatches_meta_purchase_listener()
    {
        Queue::fake([SendMetaCapiEventJob::class]);

        EcomPixel::create([
            'code' => '123456789012345',
            'access_token' => 'EAABtestToken12345',
            'status' => 1,
            'capi_status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => '998877',
            'amount' => 2500,
            'discount' => 0,
            'shipping_charge' => 100,
            'customer_id' => 1,
            'order_status' => 1,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Sizar Babu',
            'phone' => '01711223344',
            'address' => 'Mirpur, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => 10,
            'product_name' => 'Cotton Panjabi',
            'purchase_price' => 800,
            'sale_price' => 1200,
            'qty' => 2,
        ]);

        event(new OrderPlaced($order));

        Queue::assertPushed(SendMetaCapiEventJob::class, function ($job) use ($order) {
            return $job->eventName === 'Purchase'
                && $job->eventId === 'order_' . $order->invoice_id
                && $job->orderId === $order->id;
        });
    }

    public function test_admin_can_save_pixel_with_capi_access_token_and_test_code()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('pixels.store'), [
            'code' => '987654321098765',
            'access_token' => 'EAAB_permanent_system_token_xyz',
            'test_event_code' => 'TEST_ADMIN_CODE',
            'status' => 1,
            'capi_status' => 1,
        ]);

        $response->assertRedirect(route('pixels.index'));

        $this->assertDatabaseHas('ecom_pixels', [
            'code' => '987654321098765',
            'access_token' => 'EAAB_permanent_system_token_xyz',
            'test_event_code' => 'TEST_ADMIN_CODE',
            'status' => 1,
            'capi_status' => 1,
        ]);
    }

    public function test_admin_can_update_pixel_with_capi_access_token()
    {
        $this->actingAs($this->admin);

        $pixel = EcomPixel::create([
            'code' => '111222333444555',
            'access_token' => 'old_token',
            'test_event_code' => 'OLD_CODE',
            'status' => 1,
            'capi_status' => 1,
        ]);

        $response = $this->post(route('pixels.update'), [
            'id' => $pixel->id,
            'code' => '111222333444555',
            'access_token' => 'new_updated_token_xyz',
            'test_event_code' => 'NEW_CODE_123',
            'status' => 1,
            'capi_status' => 1,
        ]);

        $response->assertRedirect(route('pixels.index'));

        $this->assertDatabaseHas('ecom_pixels', [
            'id' => $pixel->id,
            'access_token' => 'new_updated_token_xyz',
            'test_event_code' => 'NEW_CODE_123',
        ]);
    }

    public function test_cart_store_dispatches_meta_capi_add_to_cart_job()
    {
        Queue::fake([SendMetaCapiEventJob::class]);

        $category = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion-' . rand(1000, 9999),
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Premium Panjabi',
            'slug' => 'premium-panjabi-' . rand(1000, 9999),
            'product_code' => 'P-' . rand(10000, 99999),
            'category_id' => $category->id,
            'new_price' => 1200,
            'purchase_price' => 700,
            'stock' => 20,
            'status' => 1,
        ]);

        $response = $this->post(route('cart.store'), [
            'id' => $product->id,
            'qty' => 2,
            'order_now' => 'কার্টে যোগ করুন',
        ]);

        Queue::assertPushed(SendMetaCapiEventJob::class, function ($job) use ($product) {
            return $job->eventName === 'AddToCart'
                && str_starts_with($job->eventId, 'cart_' . $product->id)
                && ($job->customData['value'] ?? 0) == 2400
                && ($job->customData['content_name'] ?? '') === 'Premium Panjabi';
        });
    }

    public function test_checkout_dispatches_meta_capi_initiate_checkout_job()
    {
        Queue::fake([SendMetaCapiEventJob::class]);

        $category = Category::create([
            'name' => 'Shoes',
            'slug' => 'shoes-' . rand(1000, 9999),
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Leather Loafer',
            'slug' => 'leather-loafer-' . rand(1000, 9999),
            'product_code' => 'P-' . rand(10000, 99999),
            'category_id' => $category->id,
            'new_price' => 1500,
            'purchase_price' => 900,
            'stock' => 15,
            'status' => 1,
        ]);

        ShippingCharge::create([
            'name' => 'Inside Dhaka',
            'amount' => 60,
            'status' => 1,
        ]);

        Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 1500,
            'options' => [
                'slug' => $product->slug,
                'image' => 'frontEnd/images/no-image.png',
            ],
        ]);

        $response = $this->get(route('customer.checkout'));

        $response->assertStatus(200);
        $response->assertViewHas('eventId');

        Queue::assertPushed(SendMetaCapiEventJob::class, function ($job) {
            return $job->eventName === 'InitiateCheckout'
                && str_starts_with($job->eventId, 'ic_')
                && ($job->customData['value'] ?? 0) == 1500;
        });
    }
}