<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\IncompleteOrder;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAnalyticsAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_with_analytics_and_low_stock_alerts()
    {
        $admin = User::factory()->create();

        // Create low stock product
        $product = Product::create([
            'name' => 'Low Stock Wireless Earbuds',
            'slug' => 'low-stock-earbuds-1',
            'category_id' => 1,
            'old_price' => 1500,
            'new_price' => 1200,
            'purchase_price' => 800,
            'stock' => 3,
            'pro_unit' => 'pcs',
            'product_code' => 'EP-01',
            'status' => 1,
        ]);

        $status = OrderStatus::create([
            'name' => 'Pending',
            'slug' => 'pending',
            'status' => 1,
        ]);

        $order = new Order();
        $order->invoice_id = 'DASH' . rand(1000, 9999);
        $order->amount = 2500;
        $order->discount = 0;
        $order->shipping_charge = 120;
        $order->customer_id = 1;
        $order->order_status = 1;
        $order->save();

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Low Stock Wireless Earbuds',
            'purchase_price' => 800,
            'sale_price' => 1200,
            'qty' => 2,
        ]);

        IncompleteOrder::create([
            'name' => 'Lead Buyer',
            'phone' => '01711000111',
            'address' => 'Dhaka',
            'data' => json_encode(['cart' => []]),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Low Stock Alerts');
        $response->assertSee('Delivery Success');
        $response->assertSee('Total Revenue');
        $response->assertSee('Pending Orders');
        $response->assertSee('Cart Leads');
        $response->assertSee('Top 5 Best Selling Products');
        $response->assertSee('Low Stock Wireless Earbuds');
        $response->assertSee('href="' . route('dashboard') . '"', false);
        $response->assertDontSee('href="' . route('dashboard') . '" data-bs-toggle="collapse"', false);
    }
}

