<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStockReportCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Category $categoryA;
    private Category $categoryB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $this->categoryA = Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'status' => 1,
        ]);

        $this->categoryB = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
            'status' => 1,
        ]);
    }

    public function test_guest_cannot_access_stock_report(): void
    {
        $response = $this->get(route('admin.stock_report'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_stock_report_with_kpis_and_products(): void
    {
        $p1 = Product::create([
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'product_code' => 'IPH15P',
            'category_id' => $this->categoryA->id,
            'purchase_price' => 100000,
            'old_price' => 135000,
            'new_price' => 130000,
            'stock' => 10,
            'status' => 1,
        ]);

        $p2 = Product::create([
            'name' => 'MacBook Air M2',
            'slug' => 'macbook-air-m2',
            'product_code' => 'MBA-M2',
            'category_id' => $this->categoryB->id,
            'purchase_price' => 95000,
            'old_price' => 125000,
            'new_price' => 120000,
            'stock' => 5,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.stock_report'));

        $response->assertStatus(200);
        $response->assertSee('Inventory Stock Report');
        $response->assertSee('Total SKUs');
        $response->assertSee('In-Stock Units');
        $response->assertSee('Total Cost Value');
        $response->assertSee('Total Retail Value');
        $response->assertSee('iPhone 15 Pro');
        $response->assertSee('IPH15P');
        $response->assertSee('MacBook Air M2');
        $response->assertSee('MBA-M2');
        $response->assertSee('Smartphones');
        $response->assertSee('Laptops');
    }

    public function test_admin_can_filter_stock_report_by_keyword(): void
    {
        Product::create([
            'name' => 'Samsung Galaxy S24',
            'slug' => 'samsung-galaxy-s24',
            'product_code' => 'SGS24',
            'category_id' => $this->categoryA->id,
            'purchase_price' => 80000,
            'old_price' => 105000,
            'new_price' => 100000,
            'stock' => 8,
            'status' => 1,
        ]);

        Product::create([
            'name' => 'Dell XPS 13',
            'slug' => 'dell-xps-13',
            'product_code' => 'DXPS13',
            'category_id' => $this->categoryB->id,
            'purchase_price' => 110000,
            'old_price' => 145000,
            'new_price' => 140000,
            'stock' => 3,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.stock_report', ['keyword' => 'Galaxy']));

        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S24');
        $response->assertDontSee('Dell XPS 13');
    }

    public function test_admin_can_filter_stock_report_by_category(): void
    {
        Product::create([
            'name' => 'Google Pixel 8',
            'slug' => 'google-pixel-8',
            'product_code' => 'GP8',
            'category_id' => $this->categoryA->id,
            'purchase_price' => 60000,
            'old_price' => 80000,
            'new_price' => 75000,
            'stock' => 12,
            'status' => 1,
        ]);

        Product::create([
            'name' => 'Asus ZenBook 14',
            'slug' => 'asus-zenbook-14',
            'product_code' => 'AZB14',
            'category_id' => $this->categoryB->id,
            'purchase_price' => 75000,
            'old_price' => 95000,
            'new_price' => 90000,
            'stock' => 4,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.stock_report', ['category_id' => $this->categoryB->id]));

        $response->assertStatus(200);
        $response->assertSee('Asus ZenBook 14');
        $response->assertDontSee('Google Pixel 8');
    }

    public function test_stock_report_aggregates_correct_valuation(): void
    {
        Product::create([
            'name' => 'Item Alpha',
            'slug' => 'item-alpha',
            'product_code' => 'ALPHA1',
            'category_id' => $this->categoryA->id,
            'purchase_price' => 500,
            'old_price' => 700,
            'new_price' => 600,
            'stock' => 20,
            'status' => 1,
        ]);

        Product::create([
            'name' => 'Item Beta',
            'slug' => 'item-beta',
            'product_code' => 'BETA2',
            'category_id' => $this->categoryB->id,
            'purchase_price' => 1000,
            'old_price' => 1500,
            'new_price' => 1200,
            'stock' => 10,
            'status' => 1,
        ]);

        // Total Stock: 20 + 10 = 30 Pcs
        // Total Purchase: (500 * 20) + (1000 * 10) = 10000 + 10000 = 20,000
        // Total Retail Price: (600 * 20) + (1200 * 10) = 12000 + 12000 = 24,000

        $response = $this->actingAs($this->adminUser)->get(route('admin.stock_report'));

        $response->assertStatus(200);
        $response->assertSee('30');
        $response->assertSee('20,000');
        $response->assertSee('24,000');
    }
}