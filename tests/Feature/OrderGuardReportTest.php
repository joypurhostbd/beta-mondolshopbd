<?php

namespace Tests\Feature;

use App\Models\Courierapi;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderGuardReportTest extends TestCase
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
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);

        OrderStatus::firstOrCreate(['id' => 1], ['name' => 'Pending', 'slug' => 'pending', 'status' => 1]);
    }

    public function test_admin_orders_datatable_renders_order_guard_report_column(): void
    {
        $order = Order::create([
            'invoice_id' => '10001',
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => 1,
            'order_status' => 1,
            'f_check' => 80,
            'fraud_report' => [
                'total_parcel' => 5,
                'total_delivered' => 4,
                'total_cancel' => 1,
                'delivery_rate' => 80,
                'courier_breakdown' => [
                    'Steadfast' => ['orders' => 5, 'delivered' => 4, 'canceled' => 1],
                ],
            ],
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Test Customer',
            'phone' => '01711002233',
            'address' => 'Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'all']));

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data['data']);

        $row = $data['data'][0];
        $this->assertArrayHasKey('fraud_check', $row);
        $this->assertStringContainsString('order-guard-box', $row['fraud_check']);
        $this->assertStringContainsString('btn-refresh-order-guard', $row['fraud_check']);
        $this->assertStringContainsString('ALL: <strong class="guard-all text-dark">5</strong>', $row['fraud_check']);
        $this->assertStringContainsString('DLVD: <strong class="guard-dlvd">4</strong>', $row['fraud_check']);
        $this->assertStringContainsString('CANCL: <strong class="guard-cancl">1</strong>', $row['fraud_check']);
        $this->assertStringContainsString('width: 80%', $row['fraud_check']);
    }

    public function test_admin_orders_datatable_renders_fallback_when_no_fraud_data(): void
    {
        $order = Order::create([
            'invoice_id' => '10002',
            'amount' => 850,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 2,
            'order_status' => 1,
            'f_check' => 0,
            'fraud_report' => null,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 2,
            'name' => 'New Customer',
            'phone' => '01899112233',
            'address' => 'Chittagong',
            'area' => 'Outside Dhaka',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'all']));

        $response->assertOk();
        $data = $response->json();
        $row = $data['data'][0];

        $this->assertStringContainsString('ALL: <strong class="guard-all text-dark">0</strong>', $row['fraud_check']);
        $this->assertStringContainsString('DLVD: <strong class="guard-dlvd">0</strong>', $row['fraud_check']);
        $this->assertStringContainsString('CANCL: <strong class="guard-cancl">0</strong>', $row['fraud_check']);
        $this->assertStringContainsString('btn-refresh-order-guard', $row['fraud_check']);
    }

    public function test_refresh_fraud_score_persists_fraud_report_in_database(): void
    {
        Courierapi::create([
            'type' => 'fraud',
            'token' => 'test_active_token',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => '10003',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 3,
            'order_status' => 1,
            'f_check' => 0,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 3,
            'name' => 'Recheck Customer',
            'phone' => '01912345678',
            'address' => 'Sylhet',
            'area' => 'Outside Dhaka',
        ]);

        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => Http::response([
                'Summaries' => [
                    'Steadfast' => ['Total Parcels' => 5, 'Delivered Parcels' => 4, 'Canceled Parcels' => 1],
                    'Pathao' => ['Total Delivery' => 10, 'Successful Delivery' => 8, 'Canceled Delivery' => 2],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.order.refresh_fraud', $order->id), [
            'force' => 1,
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'total_orders' => 15,
            'total_delivered' => 12,
            'total_cancel' => 3,
            'formatted_rate' => '80%',
        ]);

        $order->refresh();
        $this->assertNotNull($order->fraud_report);
        $this->assertEquals(15, $order->fraud_report['total_parcel']);
        $this->assertEquals(12, $order->fraud_report['total_delivered']);
        $this->assertEquals(3, $order->fraud_report['total_cancel']);
        $this->assertEquals(80.0, (float) $order->fraud_report['delivery_rate']);
    }
}
