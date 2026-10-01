<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminProductPriceEditTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_price_edit_page_renders_with_kpi_metrics_and_categories(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $product1 = Product::create([
            'name' => 'Wireless Mouse',
            'slug' => 'wireless-mouse',
            'category_id' => $category->id,
            'product_code' => 'WM-100',
            'old_price' => 500,
            'new_price' => 450,
            'purchase_price' => 300,
            'stock' => 15,
            'status' => 1,
        ]);

        Productimage::create([
            'product_id' => $product1->id,
            'image' => 'uploads/product/test-mouse.png',
        ]);

        $product2 = Product::create([
            'name' => 'Mechanical Keyboard',
            'slug' => 'mechanical-keyboard',
            'category_id' => $category->id,
            'product_code' => 'KB-200',
            'old_price' => 2000,
            'new_price' => 2000,
            'purchase_price' => 1500,
            'stock' => 3, // Low stock <= 5
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('products.price_edit'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertViewHas('categories');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total_active'] === 2
                && $metrics['total_stock'] === 18
                && $metrics['discounted'] === 1
                && $metrics['low_stock'] === 1;
        });

        $response->assertSee('Wireless Mouse');
        $response->assertSee('Mechanical Keyboard');
        $response->assertSee('WM-100');
        $response->assertSee('Electronics');
    }

    public function test_price_edit_filters_by_keyword_and_category(): void
    {
        $category1 = Category::create([
            'name' => 'Gadgets',
            'slug' => 'gadgets',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $category2 = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $p1 = Product::create([
            'name' => 'Smart Watch Pro',
            'slug' => 'smart-watch-pro',
            'category_id' => $category1->id,
            'product_code' => 'SW-99',
            'old_price' => 3500,
            'new_price' => 3000,
            'purchase_price' => 2200,
            'stock' => 10,
            'status' => 1,
        ]);

        $p2 = Product::create([
            'name' => 'Denim Jacket',
            'slug' => 'denim-jacket',
            'category_id' => $category2->id,
            'product_code' => 'DJ-01',
            'old_price' => 1500,
            'new_price' => 1200,
            'purchase_price' => 900,
            'stock' => 20,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('products.price_edit', ['keyword' => 'SW-99']));
        $response->assertStatus(200);
        $response->assertSee('Smart Watch Pro');
        $response->assertDontSee('Denim Jacket');

        $categoryResponse = $this->actingAs($this->adminUser)->get(route('products.price_edit', ['category_id' => $category2->id]));
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertSee('Denim Jacket');
        $categoryResponse->assertDontSee('Smart Watch Pro');
    }

    public function test_price_update_bulk_updates_prices_and_stock(): void
    {
        $product = Product::create([
            'name' => 'Running Shoes',
            'slug' => 'running-shoes',
            'category_id' => 1,
            'product_code' => 'RS-01',
            'old_price' => 2500,
            'new_price' => 2200,
            'purchase_price' => 1600,
            'stock' => 5,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('products.price_update'), [
            'ids' => [$product->id],
            'old_price' => [3000],
            'new_price' => [2400],
            'stock' => [25],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'old_price' => 3000,
            'new_price' => 2400,
            'stock' => 25,
        ]);
    }

    public function test_price_update_handles_empty_selection_safely(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('products.price_update'), [
            'ids' => [],
        ]);

        $response->assertRedirect();
    }
}
