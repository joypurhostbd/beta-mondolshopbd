<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Courierapi;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use App\Services\SteadfastCourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SteadfastCourierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $permissions = [
            'setting-list',
            'setting-create',
            'setting-edit',
            'setting-delete',
            'order-list',
            'order-edit',
        ];
        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);

        // Seed order statuses
        OrderStatus::firstOrCreate(['id' => 1], ['name' => 'Pending', 'slug' => 'pending', 'status' => 1]);
        OrderStatus::firstOrCreate(['id' => 2], ['name' => 'Confirmed', 'slug' => 'confirmed', 'status' => 1]);
        OrderStatus::firstOrCreate(['id' => 3], ['name' => 'Processing', 'slug' => 'processing', 'status' => 1]);
        OrderStatus::firstOrCreate(['id' => 4], ['name' => 'Shipped', 'slug' => 'shipped', 'status' => 1]);
        OrderStatus::firstOrCreate(['id' => 5], ['name' => 'Delivered', 'slug' => 'delivered', 'status' => 1]);
        OrderStatus::firstOrCreate(['id' => 6], ['name' => 'Cancelled', 'slug' => 'cancelled', 'status' => 1]);
    }

    public function test_steadfast_service_place_order(): void
    {
        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status' => 200,
                'message' => 'Consignment has been created successfully.',
                'consignment' => [
                    'consignment_id' => 1424107,
                    'invoice' => 'INV-1001',
                    'tracking_code' => '15BAEB8A',
                ],
            ], 200),
        ]);

        $service = (new SteadfastCourierService())->withConfig('test_api_key', 'test_secret_key');
        $res = $service->placeOrder([
            'invoice' => 'INV-1001',
            'recipient_name' => 'John Doe',
            'recipient_phone' => '01711111111',
            'recipient_address' => 'Dhaka, Bangladesh',
            'cod_amount' => 1500,
            'note' => 'Handle carefully',
        ]);

        $this->assertEquals(200, $res['status']);
        $this->assertEquals('15BAEB8A', $res['consignment']['tracking_code']);
        $this->assertEquals(1424107, $res['consignment']['consignment_id']);
    }

    public function test_steadfast_service_get_balance_and_test_connection(): void
    {
        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/get_balance' => Http::response([
                'status' => 200,
                'current_balance' => 12500.50,
            ], 200),
        ]);

        $service = (new SteadfastCourierService())->withConfig('test_api_key', 'test_secret_key');
        $testResult = $service->testConnection();

        $this->assertTrue($testResult['success']);
        $this->assertEquals(12500.50, $testResult['current_balance']);
    }

    public function test_admin_can_test_steadfast_connection_via_ajax(): void
    {
        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/get_balance' => Http::response([
                'status' => 200,
                'current_balance' => 8400.00,
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('courierapi.steadfast.test'), [
            'api_key' => 'valid_key',
            'secret_key' => 'valid_secret',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'current_balance' => 8400,
        ]);
    }

    public function test_admin_can_dispatch_single_order_to_steadfast(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'live_api_key',
            'secret_key' => 'live_secret_key',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'INV-9901',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Jane Smith',
            'phone' => '01822222222',
            'address' => 'Chittagong, Bangladesh',
            'area' => 'Inside Dhaka',
        ]);

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status' => 200,
                'consignment' => [
                    'consignment_id' => 987654,
                    'tracking_code' => 'STDF9876',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.order.send_steadfast', $order->id));

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $order->refresh();
        $this->assertEquals('987654', $order->consignment_id);
        $this->assertEquals('STDF9876', $order->tracking_code);
        $this->assertEquals(OrderStatusEnum::Shipped->value, $order->order_status);
    }

    public function test_admin_can_check_courier_status_and_sync(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'live_api_key',
            'secret_key' => 'live_secret_key',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'INV-9902',
            'amount' => 800,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'consignment_id' => '12345',
            'tracking_code' => 'TRK12345',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/status_by_trackingcode/*' => Http::response([
                'status' => 200,
                'delivery_status' => 'delivered',
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.order.courier_status', $order->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'delivery_status' => 'delivered',
            'status_changed' => true,
        ]);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Delivered->value, $order->order_status);
    }

    public function test_steadfast_webhook_updates_order_status_to_delivered(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'live_api_key',
            'secret_key' => 'live_secret_key',
            'token' => 'my_webhook_secret_123',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'INV-8888',
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'tracking_code' => 'STDF8888',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        $payload = [
            'invoice' => 'INV-8888',
            'tracking_code' => 'STDF8888',
            'status' => 'delivered',
        ];

        // 1. Without valid token -> 401
        $unauth = $this->postJson(route('api.steadfast.webhook'), $payload);
        $unauth->assertStatus(401);

        // 2. With valid bearer token -> 200
        $response = $this->withToken('my_webhook_secret_123')->postJson(route('api.steadfast.webhook'), $payload);
        $response->assertOk();
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Delivered->value, $order->order_status);
    }

    public function test_steadfast_webhook_cancellation_releases_stock(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'token' => 'my_secret',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'T-Shirt Test',
            'slug' => 't-shirt-test',
            'product_code' => 'P1001',
            'category_id' => 1,
            'purchase_price' => 200,
            'old_price' => 400,
            'new_price' => 350,
            'stock' => 10,
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'INV-7777',
            'amount' => 350,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'tracking_code' => 'STDF7777',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 200,
            'sale_price' => 350,
            'qty' => 2,
        ]);

        $payload = [
            'invoice' => $order->invoice_id,
            'status' => 'cancelled',
        ];

        $response = $this->withToken('my_secret')->postJson(route('api.steadfast.webhook'), $payload);
        $response->assertOk();

        $order->refresh();
        $product->refresh();

        $this->assertEquals(OrderStatusEnum::Cancelled->value, $order->order_status);
        $this->assertEquals(12, $product->stock); // 10 + 2 restored
    }

    public function test_admin_can_bulk_sync_steadfast_courier_status(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'live_api_key',
            'secret_key' => 'live_secret_key',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
            'status' => 1,
        ]);

        $order1 = Order::create([
            'invoice_id' => 'INV-B1',
            'amount' => 1000,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'tracking_code' => 'STDF-B1',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        $order2 = Order::create([
            'invoice_id' => 'INV-B2',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'tracking_code' => 'STDF-B2',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/status_by_trackingcode/STDF-B1' => Http::response([
                'status' => 200,
                'delivery_status' => 'delivered',
            ], 200),
            'https://portal.steadfast.com.bd/api/v1/status_by_trackingcode/STDF-B2' => Http::response([
                'status' => 200,
                'delivery_status' => 'in_review',
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.order.bulk_courier_sync'), [
            'order_ids' => [$order1->id, $order2->id],
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'success_count' => 2,
        ]);

        $order1->refresh();
        $order2->refresh();

        $this->assertEquals('delivered', $order1->courier_status);
        $this->assertEquals(OrderStatusEnum::Delivered->value, $order1->order_status);
        $this->assertEquals('in_review', $order2->courier_status);
    }

    public function test_courier_sync_status_artisan_command(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'live_api_key',
            'secret_key' => 'live_secret_key',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'INV-CMD',
            'amount' => 900,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'tracking_code' => 'STDF-CMD',
            'courier_name' => 'steadfast',
            'courier_status' => 'pending',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/status_by_trackingcode/STDF-CMD' => Http::response([
                'status' => 200,
                'delivery_status' => 'delivered',
            ], 200),
        ]);

        $this->artisan('courier:sync-status', [
            '--courier' => 'steadfast',
            '--limit' => 10,
        ])->assertSuccessful();

        $order->refresh();
        $this->assertEquals('delivered', $order->courier_status);
        $this->assertEquals(OrderStatusEnum::Delivered->value, $order->order_status);
    }

    public function test_get_orders_datatable_renders_courier_badge_and_sync_button(): void
    {
        $order = Order::create([
            'invoice_id' => 'INV-DT1',
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'tracking_code' => 'STDF-TRK99',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'all']));

        $response->assertOk();
        $content = $response->json();
        $this->assertNotEmpty($content['data']);

        $rowHtml = json_encode($content['data']);
        $this->assertStringContainsString('btn-sync-courier', $rowHtml);
        $this->assertStringContainsString('In Review', $rowHtml);
    }

    public function test_standard_and_pos_invoice_display_courier_tracking_info(): void
    {
        \App\Models\GeneralSetting::firstOrCreate(['id' => 1], [
            'name' => 'Mondol Shop BD',
            'white_logo' => 'uploads/logo.png',
            'status' => 1,
        ]);
        \App\Models\Contact::firstOrCreate(['id' => 1], [
            'phone' => '01700000000',
            'email' => 'support@mondolshopbd.com',
            'address' => 'Dhaka, Bangladesh',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'INV-INV99',
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'consignment_id' => '295017297',
            'tracking_code' => 'SFR260910STDB99693BD',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Recipient Name',
            'phone' => '01811111111',
            'address' => 'Mirpur, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        // 1. Standard Invoice
        $stdResponse = $this->actingAs($this->adminUser)->get(route('admin.order.invoice', ['invoice_id' => $order->invoice_id]));
        $stdResponse->assertOk();
        $stdResponse->assertSee('295017297');
        $stdResponse->assertSee('Tracking ID');
        $stdResponse->assertSee('SteadFast');

        // 2. POS Invoice (?pos=true)
        $posResponse = $this->actingAs($this->adminUser)->get(route('admin.order.invoice', ['invoice_id' => $order->invoice_id, 'pos' => 'true']));
        $posResponse->assertOk();
        $posResponse->assertSee('295017297');
        $posResponse->assertSee('Tracking ID');
        $posResponse->assertSee('SteadFast');
    }

    public function test_cannot_manually_update_status_for_courier_dispatched_order(): void
    {
        $order = Order::create([
            'invoice_id' => 'INV-LOCK-01',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => 1,
            'consignment_id' => 'CID-LOCK-01',
            'tracking_code' => 'TRK-LOCK-01',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.order.update-status'), [
            'order_id' => $order->id,
            'status_id' => OrderStatusEnum::Delivered->value,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('dispatched to courier', $response->json('msg'));

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Shipped->value, $order->order_status);
    }

    public function test_bulk_status_skips_courier_dispatched_orders(): void
    {
        $dispatchedOrder = Order::create([
            'invoice_id' => 'INV-BULK-DISP',
            'amount' => 1000,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'consignment_id' => 'CID-BULK-01',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        $normalOrder = Order::create([
            'invoice_id' => 'INV-BULK-NORM',
            'amount' => 800,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.order.status', [
            'order_ids' => [$dispatchedOrder->id, $normalOrder->id],
            'order_status' => OrderStatusEnum::Processing->value,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('skipped', $response->json('message'));

        $dispatchedOrder->refresh();
        $normalOrder->refresh();

        // Dispatched order remains Shipped (locked)
        $this->assertEquals(OrderStatusEnum::Shipped->value, $dispatchedOrder->order_status);
        // Normal order updated to Processing
        $this->assertEquals(OrderStatusEnum::Processing->value, $normalOrder->order_status);
    }

    public function test_datatable_renders_single_line_locked_courier_status(): void
    {
        $order = Order::create([
            'invoice_id' => 'INV-LINE-01',
            'amount' => 950,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'consignment_id' => 'CID-LINE-01',
            'tracking_code' => 'TRK-LINE-01',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
            'order_status' => OrderStatusEnum::Shipped->value,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'all']));
        $response->assertOk();

        $content = json_encode($response->json('data'));
        $this->assertStringContainsString('btn-sync-courier', $content);
        $this->assertStringContainsString('fe-lock', $content);
        $this->assertStringContainsString('In Review', $content);
        // It must be an input-group, not a separate line div
        $this->assertStringContainsString('input-group', $content);
    }

    public function test_web_cron_courier_sync_requires_valid_key_and_syncs_orders(): void
    {
        // 1. Unauthorized without key
        $unauth = $this->getJson(route('api.courier.cron'));
        $unauth->assertStatus(401);

        // 2. Authorized with key
        $auth = $this->getJson(route('api.courier.cron', ['key' => 'mondolshopbd_courier_cron_secret']));
        $auth->assertOk();
        $auth->assertJsonStructure([
            'success',
            'message',
            'checked',
            'updated',
            'failed',
        ]);
    }
}

