<?php

namespace Tests\Feature;

use App\Models\Courierapi;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Shipping;
use App\Models\User;
use App\Services\FraudCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderFraudKpiTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $permissions = ['order-list', 'order-edit', 'setting-list'];
        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);

        // Seed basic order status
        OrderStatus::firstOrCreate(['id' => 1], ['name' => 'Pending', 'slug' => 'pending', 'status' => 1]);
    }

    public function test_fraud_check_service_parses_courier_breakdown_correctly(): void
    {
        $service = new FraudCheckService();

        $apiData = [
            'Summaries' => [
                'Steadfast' => ['Total Parcels' => 0, 'Delivered Parcels' => 0, 'Canceled Parcels' => 0],
                'RedX' => ['Total Parcels' => 0, 'Delivered Parcels' => 0, 'Canceled Parcels' => 0],
                'Pathao' => ['Total Delivery' => 11, 'Successful Delivery' => 9, 'Canceled Delivery' => 2],
                'Carrybee' => ['Total Parcels' => 0, 'Delivered Parcels' => 0, 'Canceled Parcels' => 0],
            ],
        ];

        $breakdown = $service->parseCourierBreakdown($apiData);

        $this->assertArrayHasKey('Steadfast', $breakdown);
        $this->assertArrayHasKey('RedX', $breakdown);
        $this->assertArrayHasKey('Pathao', $breakdown);
        $this->assertArrayHasKey('Carrybee', $breakdown);

        $this->assertSame(11, $breakdown['Pathao']['orders']);
        $this->assertSame(9, $breakdown['Pathao']['delivered']);
        $this->assertSame(2, $breakdown['Pathao']['canceled']);
        $this->assertSame(18, $breakdown['Pathao']['return_rate']);
        $this->assertSame(82, $breakdown['Pathao']['success_rate']);

        $this->assertSame(0, $breakdown['Steadfast']['orders']);
        $this->assertSame(0, $breakdown['Steadfast']['return_rate']);
    }

    public function test_order_edit_view_receives_fraud_check_data(): void
    {
        $order = Order::create([
            'invoice_id' => '202334773',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => 1,
            'f_check' => 82,
        ]);

        $shipping = Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Test Customer',
            'phone' => '01711223344',
            'address' => 'Dhaka, Bangladesh',
            'area' => 'Inside Dhaka',
        ]);

        $order->update(['shipping_id' => $shipping->id]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.order.edit', $order->invoice_id));

        $response->assertOk();
        $response->assertViewHas('fraudCheckData');
        $response->assertSee('Courier Delivery History (Fraud Check)');
        $response->assertSee('Success Ratio');
        $response->assertSee('Steadfast');
        $response->assertSee('Pathao');
    }

    public function test_refresh_fraud_score_endpoint_returns_courier_breakdown_and_totals(): void
    {
        Courierapi::create([
            'type' => 'fraud',
            'token' => 'active_token_123',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => '202334773',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => 1,
            'f_check' => 0,
        ]);

        $shipping = Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Test Customer',
            'phone' => '01711223344',
            'address' => 'Dhaka, Bangladesh',
            'area' => 'Inside Dhaka',
        ]);

        $order->update(['shipping_id' => $shipping->id]);

        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => Http::response([
                'Summaries' => [
                    'Pathao' => ['Total Delivery' => 11, 'Successful Delivery' => 9, 'Canceled Delivery' => 2],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(url("admin/order/refresh-fraud/{$order->id}"), [
            'force' => 1,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'formatted_rate' => '82%',
            'total_orders' => 11,
            'total_delivered' => 9,
            'total_cancel' => 2,
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('courier_breakdown', $data);
        $this->assertSame(11, $data['courier_breakdown']['Pathao']['orders']);
        $this->assertSame(9, $data['courier_breakdown']['Pathao']['delivered']);
        $this->assertSame(18, $data['courier_breakdown']['Pathao']['return_rate']);

        // Check DB was updated
        $order->refresh();
        $this->assertEquals(81.82, (float) $order->f_check);
    }

    public function test_fraud_check_service_get_cached_returns_cached_data_or_null(): void
    {
        $service = new FraudCheckService();
        $phone = '01799887766';

        // Initially null
        $this->assertNull($service->getCached($phone));

        // When cached
        $cacheData = [
            'total_stats' => ['delivery_rate' => 90, 'total_parcel' => 10, 'total_delivered' => 9],
            'courier_breakdown' => $service->parseCourierBreakdown(null),
            'status' => 'success',
        ];
        \Illuminate\Support\Facades\Cache::put("fraud_check_01799887766", $cacheData, 1800);

        $cached = $service->getCached($phone);
        $this->assertNotNull($cached);
        $this->assertSame(90, $cached['total_stats']['delivery_rate']);
    }

    public function test_order_edit_page_does_not_make_external_http_calls_on_initial_load(): void
    {
        Http::fake();

        $order = Order::create([
            'invoice_id' => '202334774',
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => 1,
            'f_check' => 75,
        ]);

        $shipping = Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Instant Load Test',
            'phone' => '01888776655',
            'address' => 'Dhaka, Bangladesh',
            'area' => 'Inside Dhaka',
        ]);

        $order->update(['shipping_id' => $shipping->id]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.order.edit', $order->invoice_id));

        $response->assertOk();
        // Assert zero HTTP calls were made during page load (instant & non-blocking)
        Http::assertNothingSent();
        $response->assertSee('Courier Delivery History (Fraud Check)');
    }

    public function test_background_auto_sync_endpoint_with_force_zero_respects_cache(): void
    {
        Courierapi::create([
            'type' => 'fraud',
            'token' => 'active_token_123',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => '202334775',
            'amount' => 2000,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => 1,
            'f_check' => 0,
        ]);

        $shipping = Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Auto Sync Test',
            'phone' => '01911223344',
            'address' => 'Chittagong, Bangladesh',
            'area' => 'Outside Dhaka',
        ]);

        $order->update(['shipping_id' => $shipping->id]);

        // Pre-populate cache
        $cachedResult = [
            'total_stats' => [
                'total_parcel' => 20,
                'total_delivered' => 18,
                'total_cancel' => 2,
                'delivery_rate' => 90,
            ],
            'courier_breakdown' => [
                'Steadfast' => ['orders' => 20, 'delivered' => 18, 'canceled' => 2, 'return_rate' => 10, 'success_rate' => 90],
                'RedX' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
                'Pathao' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
                'Carrybee' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0, 'success_rate' => 0],
            ],
            'individual_response' => null,
            'status' => 'success',
        ];
        \Illuminate\Support\Facades\Cache::put('fraud_check_01911223344', $cachedResult, 1800);

        Http::fake();

        // Call auto-sync with force = 0
        $response = $this->actingAs($this->adminUser)->postJson(url("admin/order/refresh-fraud/{$order->id}"), [
            'force' => 0,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'delivery_rate' => 90,
            'total_orders' => 20,
            'total_delivered' => 18,
        ]);

        // Verify no remote API call was triggered because cache was valid
        Http::assertNothingSent();
    }
}
