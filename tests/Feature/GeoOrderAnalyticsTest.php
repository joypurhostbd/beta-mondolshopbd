<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use App\Services\GeoOrderAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoOrderAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_geo_resolver_correctly_identifies_districts_and_thanas_in_bangla_and_english()
    {
        $service = app(GeoOrderAnalyticsService::class);

        // English address
        $res1 = $service->resolveFromAddress("Malumghat highway police fari, Chakoria, Cox'sBazar");
        $this->assertEquals("Cox's Bazar", $res1['district']);
        $this->assertEquals("Chakoria", $res1['thana']);

        // Bangla address
        $res2 = $service->resolveFromAddress("বড়বাজার চুয়াডাঙ্গা");
        $this->assertEquals("Chuadanga", $res2['district']);

        // Dhaka thana address
        $res3 = $service->resolveFromAddress("House 12, Road 4, Mirpur 10, Dhaka");
        $this->assertEquals("Dhaka", $res3['district']);
        $this->assertEquals("Mirpur", $res3['thana']);

        // Shipping area fallback
        $res4 = $service->resolveFromAddress("Bashundhara R/A", "ঢাকার ভিতরে ৭০ টাকা");
        $this->assertEquals("Dhaka", $res4['district']);
        $this->assertEquals("Bashundhara R/A", $res4['thana']);
    }

    public function test_shipping_model_automatically_resolves_district_and_thana_on_saving()
    {
        $order = Order::create([
            'invoice_id' => 'GEO' . rand(1000, 9999),
            'amount' => 1500,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => 1,
            'order_status' => 1,
        ]);

        $shipping = Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'address' => 'Chakoria, Cox\'s Bazar',
            'area' => 'ঢাকার বাহিরে ১২০ টাকা',
        ]);

        $this->assertEquals("Cox's Bazar", $shipping->district);
        $this->assertEquals("Chakoria", $shipping->thana);
    }

    public function test_admin_dashboard_renders_geographic_order_analytics_kpi()
    {
        $admin = User::factory()->create();

        // Create low stock product so dashboard doesn't fail on related widgets
        Product::create([
            'name' => 'Demo Earbuds',
            'slug' => 'demo-earbuds',
            'category_id' => 1,
            'old_price' => 1500,
            'new_price' => 1200,
            'purchase_price' => 800,
            'stock' => 10,
            'pro_unit' => 'pcs',
            'product_code' => 'DEMO-01',
            'status' => 1,
        ]);

        OrderStatus::create([
            'name' => 'Pending',
            'slug' => 'pending',
            'status' => 1,
        ]);

        $order = Order::create([
            'invoice_id' => 'GEO' . rand(1000, 9999),
            'amount' => 2000,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => 1,
            'order_status' => 1,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Rahim',
            'phone' => '01800000000',
            'address' => 'Mirpur, Dhaka',
            'area' => 'ঢাকার ভিতরে ৭০ টাকা',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Geographic Order Analytics');
        $response->assertSee('Top District (শীর্ষ জেলা)');
        $response->assertSee('Top Thana/Area (শীর্ষ থানা)');
        $response->assertSee('Dhaka');
    }

    public function test_admin_can_filter_geo_analytics_via_ajax()
    {
        $admin = User::factory()->create();

        $order = Order::create([
            'invoice_id' => 'GEO' . rand(1000, 9999),
            'amount' => 3000,
            'discount' => 0,
            'shipping_charge' => 120,
            'customer_id' => 1,
            'order_status' => 1,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Karim',
            'phone' => '01900000000',
            'address' => 'Gouripor bazar, Daudkandi, Cumilla',
            'area' => 'ঢাকার বাহিরে ১২০ টাকা',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.dashboard.geo_analytics', [
            'period' => 'all_time',
            'status' => 'all',
            'district' => 'Cumilla'
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'kpis' => [
                'total_orders',
                'geocoded_orders',
                'geocoded_rate',
                'top_district',
                'top_thana',
            ],
            'top_districts',
            'top_thanas',
            'available_districts',
        ]);

        $this->assertEquals('Cumilla', $response->json('kpis.top_district'));
    }
}
