<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shipping\Application\Actions\SyncCourierOrderStatusAction;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminGranularCourierStatusesTest extends TestCase
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
            if (! $role->hasPermissionTo($permission)) {
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
        OrderStatus::firstOrCreate(['slug' => 'on-hold'], ['name' => 'On Hold', 'status' => 1]);

        // Seed all 11 courier filter statuses
        foreach (Order::getCourierFilterDefinitions() as $slug => $def) {
            OrderStatus::firstOrCreate(['slug' => $slug], ['name' => $def['name'], 'status' => 1]);
        }
    }

    private function createSampleOrder(array $attributes = []): Order
    {
        static $phoneIndex = 2000;
        $phoneIndex++;
        $customer = Customer::create([
            'name' => 'Customer '.$phoneIndex,
            'slug' => 'customer-'.$phoneIndex,
            'phone' => '01811'.$phoneIndex,
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $order = Order::create(array_merge([
            'invoice_id' => 'INV-'.rand(10000, 99999),
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 80,
            'customer_id' => $customer->id,
            'order_status' => 1,
        ], $attributes));

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => 'Sample Address, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        return $order;
    }

    public function test_all_11_courier_filter_pages_render_successfully(): void
    {
        $courierSlugs = Order::getCourierFilterSlugs();
        $this->assertCount(11, $courierSlugs);

        foreach ($courierSlugs as $slug) {
            $response = $this->actingAs($this->adminUser)->get(route('admin.orders', ['slug' => $slug]));
            $response->assertOk();
            $response->assertSee(Order::getCourierFilterName($slug));
        }
    }

    public function test_granular_courier_statuses_filter_correctly_via_ajax(): void
    {
        // 1. In Review
        $inReview = $this->createSampleOrder([
            'consignment_id' => 'CID-REV-01',
            'courier_status' => 'in_review',
            'courier_name' => 'steadfast',
        ]);

        // 2. In Transit
        $inTransit = $this->createSampleOrder([
            'consignment_id' => 'CID-TRN-02',
            'courier_status' => 'in_transit',
            'courier_name' => 'steadfast',
        ]);

        // 3. Shipped
        $shipped = $this->createSampleOrder([
            'consignment_id' => 'CID-SHP-03',
            'courier_status' => 'shipped',
            'courier_name' => 'steadfast',
        ]);

        // 4. Picked
        $picked = $this->createSampleOrder([
            'consignment_id' => 'CID-PCK-04',
            'courier_status' => 'picked',
            'courier_name' => 'steadfast',
        ]);

        // 5. Hold
        $hold = $this->createSampleOrder([
            'consignment_id' => 'CID-HLD-05',
            'courier_status' => 'hold',
            'courier_name' => 'steadfast',
        ]);

        // 6. Returned
        $returned = $this->createSampleOrder([
            'consignment_id' => 'CID-RET-06',
            'courier_status' => 'returned',
            'courier_name' => 'steadfast',
        ]);

        // 7. Cancelled Approval Pending
        $pendingCancel = $this->createSampleOrder([
            'consignment_id' => 'CID-CAP-07',
            'courier_status' => 'cancelled_approval_pending',
            'courier_name' => 'steadfast',
        ]);

        // Verify In Review Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-in-review']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-REV-01', $res->json('data.0.invoice_id'));

        // Verify In Transit Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-in-transit']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-TRN-02', $res->json('data.0.invoice_id'));

        // Verify Shipped Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-shipped']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-SHP-03', $res->json('data.0.invoice_id'));

        // Verify Picked Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-picked']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-PCK-04', $res->json('data.0.invoice_id'));

        // Verify Hold Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-hold']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-HLD-05', $res->json('data.0.invoice_id'));

        // Verify Returned Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-returned']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-RET-06', $res->json('data.0.invoice_id'));

        // Verify Cancelled Approval Pending Ajax
        $res = $this->actingAs($this->adminUser)->getJson(route('admin.order.data', ['slug' => 'courier-cancelled-approval-pending']));
        $res->assertOk();
        $this->assertEquals(1, $res->json('recordsTotal'));
        $this->assertStringContainsString('CID-CAP-07', $res->json('data.0.invoice_id'));
    }

    public function test_get_courier_counts_returns_aggregated_metrics(): void
    {
        $this->createSampleOrder(['consignment_id' => 'C-1', 'courier_status' => 'pending']);
        $this->createSampleOrder(['consignment_id' => 'C-2', 'courier_status' => 'in_review']);
        $this->createSampleOrder(['consignment_id' => 'C-3', 'courier_status' => 'in_transit']);
        $this->createSampleOrder(['consignment_id' => 'C-4', 'courier_status' => 'shipped']);
        $this->createSampleOrder(['consignment_id' => 'C-5', 'courier_status' => 'picked']);
        $this->createSampleOrder(['consignment_id' => 'C-6', 'courier_status' => 'hold']);
        $this->createSampleOrder(['consignment_id' => 'C-7', 'courier_status' => 'partial_delivered']);
        $this->createSampleOrder(['consignment_id' => 'C-8', 'courier_status' => 'cancelled']);
        $this->createSampleOrder(['consignment_id' => 'C-9', 'courier_status' => 'returned']);
        $this->createSampleOrder(['consignment_id' => 'C-10', 'courier_status' => 'cancelled_approval_pending']);
        $this->createSampleOrder(['consignment_id' => 'C-11', 'courier_status' => 'delivered']);

        $counts = Order::getCourierCounts();

        $this->assertEquals(1, $counts['courier-pending']);
        $this->assertEquals(1, $counts['courier-in-review']);
        $this->assertEquals(1, $counts['courier-in-transit']);
        $this->assertEquals(1, $counts['courier-shipped']);
        $this->assertEquals(1, $counts['courier-picked']);
        $this->assertEquals(1, $counts['courier-hold']);
        $this->assertEquals(1, $counts['courier-partial']);
        $this->assertEquals(1, $counts['courier-cancel']);
        $this->assertEquals(1, $counts['courier-returned']);
        $this->assertEquals(1, $counts['courier-cancelled-approval-pending']);
        $this->assertEquals(1, $counts['courier-delivered']);
    }

    public function test_product_stock_deducts_on_delivered_and_restores_on_returned(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'test-cat'],
            ['name' => 'Test Cat', 'status' => 1]
        );

        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-prod-'.rand(100, 999),
            'product_code' => 'PC-'.rand(1000, 9999),
            'category_id' => $category->id,
            'purchase_price' => 500,
            'old_price' => 800,
            'new_price' => 750,
            'stock' => 20,
            'status' => 1,
        ]);

        $order = $this->createSampleOrder([
            'amount' => 750,
            'consignment_id' => 'CID-STOCK-TEST',
            'courier_status' => 'shipped',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 500,
            'sale_price' => 750,
            'qty' => 3,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'payment_method' => 'Cash On Delivery',
            'amount' => 750,
            'payment_status' => 'pending',
        ]);

        $syncAction = app(SyncCourierOrderStatusAction::class);

        // 1. Transition to delivered
        $syncAction->execute($order, 'delivered');
        $this->assertEquals('delivered', $order->fresh()->courier_status);
        $this->assertEquals(17, $product->fresh()->stock); // 20 - 3 = 17
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // 2. Transition to returned (customer returned item)
        $syncAction->execute($order, 'returned');
        $this->assertEquals('returned', $order->fresh()->courier_status);
        $this->assertEquals(20, $product->fresh()->stock); // restored 17 + 3 = 20
        $this->assertEquals('cancelled', $order->fresh()->payment_status);
    }
}
