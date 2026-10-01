<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendCustomerCourierFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected Customer $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        OrderStatus::firstOrCreate(['slug' => 'pending'], ['name' => 'Pending', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'in-courier'], ['name' => 'In Courier', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'completed'], ['name' => 'Completed', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'cancelled'], ['name' => 'Cancelled', 'status' => 1]);

        OrderStatus::firstOrCreate(['slug' => 'courier-pending'], ['name' => 'Courier Pending', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'courier-partial'], ['name' => 'Courier Partial', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'courier-cancel'], ['name' => 'Courier Cancel', 'status' => 1]);
        OrderStatus::firstOrCreate(['slug' => 'courier-delivered'], ['name' => 'Courier Delivered', 'status' => 1]);

        $this->customer = Customer::create([
            'name' => 'Customer A',
            'slug' => 'customer-a',
            'phone' => '01711000001',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $this->otherCustomer = Customer::create([
            'name' => 'Customer B',
            'slug' => 'customer-b',
            'phone' => '01711000002',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
    }

    private function createOrderForCustomer(Customer $cust, array $attributes = []): Order
    {
        $order = Order::create(array_merge([
            'invoice_id' => 'INV-' . rand(10000, 99999),
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 60,
            'customer_id' => $cust->id,
            'order_status' => 1,
        ], $attributes));

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $cust->id,
            'name' => $cust->name,
            'phone' => $cust->phone,
            'address' => 'Dhaka, Bangladesh',
            'area' => 'Inside Dhaka',
        ]);

        return $order;
    }

    public function test_customer_orders_page_displays_courier_sidebar_and_filter_tabs(): void
    {
        $this->createOrderForCustomer($this->customer, [
            'consignment_id' => 'CID-CP-101',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->actingAs($this->customer, 'customer')->get(route('customer.orders'));
        $response->assertOk();

        // Check sidebar links
        $response->assertSee(route('customer.orders', 'courier-pending'));
        $response->assertSee(route('customer.orders', 'courier-partial'));
        $response->assertSee(route('customer.orders', 'courier-cancel'));
        $response->assertSee(route('customer.orders', 'courier-delivered'));

        // Check courier partner name in table
        $response->assertSee('SteadFast');
        $response->assertSee('CID-CP-101');
    }

    public function test_customer_can_filter_orders_by_courier_status(): void
    {
        $orderPending = $this->createOrderForCustomer($this->customer, [
            'consignment_id' => 'CID-FILTER-PENDING',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $orderDelivered = $this->createOrderForCustomer($this->customer, [
            'consignment_id' => 'CID-FILTER-DELIVERED',
            'courier_status' => 'delivered',
            'courier_name' => 'steadfast',
        ]);

        // Filter by courier-pending
        $responsePending = $this->actingAs($this->customer, 'customer')->get(route('customer.orders', ['slug' => 'courier-pending']));
        $responsePending->assertOk();
        $responsePending->assertSee('CID-FILTER-PENDING');
        $responsePending->assertDontSee('CID-FILTER-DELIVERED');

        // Filter by courier-delivered
        $responseDelivered = $this->actingAs($this->customer, 'customer')->get(route('customer.orders', ['slug' => 'courier-delivered']));
        $responseDelivered->assertOk();
        $responseDelivered->assertSee('CID-FILTER-DELIVERED');
        $responseDelivered->assertDontSee('CID-FILTER-PENDING');
    }

    public function test_customer_cannot_see_other_customer_orders_in_courier_filters(): void
    {
        $otherOrder = $this->createOrderForCustomer($this->otherCustomer, [
            'consignment_id' => 'CID-OTHER-CUST',
            'courier_status' => 'pending',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->actingAs($this->customer, 'customer')->get(route('customer.orders', ['slug' => 'courier-pending']));
        $response->assertOk();
        $response->assertDontSee('CID-OTHER-CUST');
    }

    public function test_order_tracking_page_displays_courier_partner_name_and_status(): void
    {
        $order = $this->createOrderForCustomer($this->customer, [
            'invoice_id' => 'INV-TRACK-1234',
            'consignment_id' => 'CID-TRACK-999',
            'courier_status' => 'in_transit',
            'courier_name' => 'steadfast',
        ]);

        $response = $this->get(route('customer.order_track_result', [
            'phone' => $this->customer->phone,
            'invoice_id' => $order->invoice_id,
        ]));

        $response->assertOk();
        $response->assertSee('Courier:');
        $response->assertSee('SteadFast');
        $response->assertSee('In Transit');
        $response->assertSee('CID-TRACK-999');
    }
}