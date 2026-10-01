<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderReportCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected OrderStatus $statusPending;
    protected OrderStatus $statusDelivered;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'status' => 1,
        ]);

        $this->statusPending = OrderStatus::create([
            'name' => 'Pending',
            'slug' => 'pending',
            'status' => 1,
        ]);

        $this->statusDelivered = OrderStatus::create([
            'name' => 'Delivered',
            'slug' => 'delivered',
            'status' => 1,
        ]);
    }

    public function test_guest_is_redirected_from_order_report()
    {
        $response = $this->get(route('admin.order_report'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_order_report()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.order_report'));
        $response->assertStatus(200);
        $response->assertSee('Order Report');
        $response->assertSee('Total Orders');
        $response->assertSee('Total Sales Value');
    }

    public function test_order_report_displays_orders_and_kpi_metrics()
    {
        $order = Order::create([
            'invoice_id' => '1001',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 100,
            'customer_id' => 1,
            'order_status' => $this->statusDelivered->id,
            'user_id' => $this->admin->id,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Rahim Ahmed',
            'phone' => '01711111111',
            'address' => 'Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'product_name' => 'Premium Cotton Shirt',
            'purchase_price' => 500,
            'sale_price' => 1000,
            'qty' => 2,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.order_report'));
        $response->assertStatus(200);
        $response->assertSee('Premium Cotton Shirt');
        $response->assertSee('Rahim Ahmed');
        $response->assertSee('01711111111');
        $response->assertSee('Delivered');
        $response->assertSee('2,000.00'); // total sales value
    }

    public function test_order_report_filters_by_keyword()
    {
        $order1 = Order::create([
            'invoice_id' => 'INV-101',
            'amount' => 500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => $this->statusPending->id,
            'user_id' => $this->admin->id,
        ]);
        Shipping::create([
            'order_id' => $order1->id,
            'customer_id' => 1,
            'name' => 'Alice Rahman',
            'phone' => '01811111111',
            'address' => 'Chattogram',
            'area' => 'Outside Dhaka',
        ]);
        OrderDetails::create([
            'order_id' => $order1->id,
            'product_id' => 1,
            'product_name' => 'Silk Saree Blue',
            'purchase_price' => 400,
            'sale_price' => 800,
            'qty' => 1,
        ]);

        $order2 = Order::create([
            'invoice_id' => 'INV-102',
            'amount' => 900,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 2,
            'order_status' => $this->statusDelivered->id,
            'user_id' => $this->admin->id,
        ]);
        Shipping::create([
            'order_id' => $order2->id,
            'customer_id' => 2,
            'name' => 'Bob Karim',
            'phone' => '01922222222',
            'address' => 'Sylhet',
            'area' => 'Outside Dhaka',
        ]);
        OrderDetails::create([
            'order_id' => $order2->id,
            'product_id' => 2,
            'product_name' => 'Leather Shoes Brown',
            'purchase_price' => 600,
            'sale_price' => 1200,
            'qty' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.order_report', ['keyword' => 'Silk Saree']));
        $response->assertStatus(200);
        $response->assertSee('Silk Saree Blue');
        $response->assertDontSee('Leather Shoes Brown');
    }

    public function test_order_report_filters_by_order_status()
    {
        $order1 = Order::create([
            'invoice_id' => 'INV-201',
            'amount' => 500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 1,
            'order_status' => $this->statusPending->id,
            'user_id' => $this->admin->id,
        ]);
        Shipping::create([
            'order_id' => $order1->id,
            'customer_id' => 1,
            'name' => 'Customer One',
            'phone' => '01700000001',
            'address' => 'Dhaka',
            'area' => 'Inside Dhaka',
        ]);
        OrderDetails::create([
            'order_id' => $order1->id,
            'product_id' => 1,
            'product_name' => 'Pending Item',
            'purchase_price' => 100,
            'sale_price' => 200,
            'qty' => 1,
        ]);

        $order2 = Order::create([
            'invoice_id' => 'INV-202',
            'amount' => 600,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => 2,
            'order_status' => $this->statusDelivered->id,
            'user_id' => $this->admin->id,
        ]);
        Shipping::create([
            'order_id' => $order2->id,
            'customer_id' => 2,
            'name' => 'Customer Two',
            'phone' => '01700000002',
            'address' => 'Khulna',
            'area' => 'Outside Dhaka',
        ]);
        OrderDetails::create([
            'order_id' => $order2->id,
            'product_id' => 2,
            'product_name' => 'Delivered Item',
            'purchase_price' => 150,
            'sale_price' => 300,
            'qty' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.order_report', ['order_status' => $this->statusDelivered->id]));
        $response->assertStatus(200);
        $response->assertSee('Delivered Item');
        $response->assertDontSee('Pending Item');
    }
}