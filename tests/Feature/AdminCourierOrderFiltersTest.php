<?php

namespace Tests\Feature;

use App\Models\Courierapi;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCourierOrderFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $permissions = [
            'order-list',
            'order-edit',
            'order-create',
            'order-delete',
        ];
        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);

        // Core statuses
        OrderStatus::firstOrCreate(['slug' => 'pending'], ['name' => 'Pending', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'in-courier'], ['name' => 'In Courier', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'completed'], ['name' => 'Completed', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'cancelled'], ['name' => 'Cancelled', 'status' => 1]);

        // Courier filter statuses from migration
        OrderStatus::firstOrCreate(['slug' => 'courier-pending'], ['name' => 'Courier Pending', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'courier-partial'], ['name' => 'Courier Partial', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'courier-cancel'], ['name' => 'Courier Cancel', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'courier-delivered'], ['name' => 'Courier Delivered', 'status' => 1]);
    }

    private function createSampleOrder(array $attributes = []): Order
    {
        static $phoneIndex = 1000;
        $phoneIndex++;
        $customer = Customer::create([
            'name' => 'Test Customer',
            'slug' => 'test-customer-' . $phoneIndex,
            'phone' => '01711' . $phoneIndex,
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $order = Order::create(array_merge([
            'invoice_id' => 'INV-' . rand(10000, 99999),
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => 1,
        ], $attributes));

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => 'Test Customer',
            'phone' => '01711223344',
            'address' => 'Test Address, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        return $order;
    }

    public function test_courier_pending_page_and_ajax_filters_correct_orders(): void
    {
        $pendingOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-PENDING-001',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $deliveredOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-DELIVERED-002',
            'courier_status' => 'delivered',
            'courier_name' => 'steadfast',
        ]);

        // 1. Check Page View
        $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => 'courier-pending']));
        $response->assertOk();
        $response->assertSee('Courier Pending');

        // 2. Check Ajax DataTable Endpoint
        $ajaxResponse = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-pending']));
        $ajaxResponse->assertOk();
        $data = $ajaxResponse->json();

        $this->assertEquals(1, $data['recordsTotal']);
        $this->assertStringContainsString('CID-PENDING-001', $data['data'][0]['invoice_id']);
    }

    public function test_courier_partial_page_and_ajax_filters_correct_orders(): void
    {
        $partialOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-PARTIAL-001',
            'courier_status' => 'partial_delivered',
            'courier_name' => 'steadfast',
        ]);

        $deliveredOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-DELIVERED-002',
            'courier_status' => 'delivered',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => 'courier-partial']));
        $response->assertOk();
        $response->assertSee('Courier Partial');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-partial']));
        $ajaxResponse->assertOk();
        $data = $ajaxResponse->json();

        $this->assertEquals(1, $data['recordsTotal']);
        $this->assertStringContainsString('CID-PARTIAL-001', $data['data'][0]['invoice_id']);
    }

    public function test_courier_cancel_page_and_ajax_filters_correct_orders(): void
    {
        $cancelOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-CANCEL-001',
            'courier_status' => 'cancelled',
            'courier_name' => 'steadfast',
        ]);

        $pendingOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-PENDING-002',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => 'courier-cancel']));
        $response->assertOk();
        $response->assertSee('Courier Cancel');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-cancel']));
        $ajaxResponse->assertOk();
        $data = $ajaxResponse->json();

        $this->assertEquals(1, $data['recordsTotal']);
        $this->assertStringContainsString('CID-CANCEL-001', $data['data'][0]['invoice_id']);
    }

    public function test_courier_delivered_page_and_ajax_filters_correct_orders(): void
    {
        $deliveredOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-DELIVERED-001',
            'courier_status' => 'delivered',
            'courier_name' => 'steadfast',
        ]);

        $cancelOrder = $this->createSampleOrder([
            'consignment_id' => 'CID-CANCEL-002',
            'courier_status' => 'cancelled',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => 'courier-delivered']));
        $response->assertOk();
        $response->assertSee('Courier Delivered');

        $ajaxResponse = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-delivered']));
        $ajaxResponse->assertOk();
        $data = $ajaxResponse->json();

        $this->assertEquals(1, $data['recordsTotal']);
        $this->assertStringContainsString('CID-DELIVERED-001', $data['data'][0]['invoice_id']);
    }

    public function test_sidebar_and_navigation_tabs_render_all_courier_filter_links(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => 'all']));
        $response->assertOk();

        $response->assertSee(route('admin.orders', ['slug' => 'courier-pending']));
        $response->assertSee(route('admin.orders', ['slug' => 'courier-partial']));
        $response->assertSee(route('admin.orders', ['slug' => 'courier-cancel']));
        $response->assertSee(route('admin.orders', ['slug' => 'courier-delivered']));
        $response->assertSee('Courier Pending');
        $response->assertSee('Courier Partial');
        $response->assertSee('Courier Cancel');
        $response->assertSee('Courier Delivered');
    }

    public function test_courier_filter_calculates_accurate_order_count_and_amount_metrics(): void
    {
        $this->createSampleOrder([
            'amount' => 1500,
            'consignment_id' => 'CID-METRIC-1',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $this->createSampleOrder([
            'amount' => 2500,
            'consignment_id' => 'CID-METRIC-2',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $this->createSampleOrder([
            'amount' => 3000,
            'consignment_id' => 'CID-METRIC-3',
            'courier_status' => 'delivered',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => 'courier-pending']));
        $response->assertOk();

        // 2 pending orders of 1500 + 2500 = 4000
        $response->assertSee('Courier Pending Orders (<span id="totalOrders">2</span>)', false);
        $response->assertSee('4,000.00');
    }
}
