<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminBrandCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_brand_manage_renders_with_kpi_metrics_and_product_counts(): void
    {
        $brand1 = Brand::create([
            'name' => 'Apple',
            'name_bn' => 'অ্যাপল',
            'slug' => 'apple',
            'status' => 1,
        ]);

        $brand2 = Brand::create([
            'name' => 'Samsung',
            'name_bn' => 'স্যামসাং',
            'slug' => 'samsung',
            'status' => 0,
        ]);

        Product::create([
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'category_id' => 1,
            'brand_id' => $brand1->id,
            'product_code' => 'IP-15',
            'old_price' => 120000,
            'new_price' => 110000,
            'purchase_price' => 95000,
            'stock' => 10,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('brands.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['inactive'] === 1
                && $metrics['with_products'] === 1;
        });

        $response->assertSee('Apple');
        $response->assertSee('Samsung');
        $response->assertSee('1 Products');
    }

    public function test_admin_brand_can_toggle_active_and_inactive(): void
    {
        $brand = Brand::create([
            'name' => 'Sony',
            'name_bn' => 'সনি',
            'slug' => 'sony',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('brands.inactive'), [
            'hidden_id' => $brand->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('brands.active'), [
            'hidden_id' => $brand->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'status' => 1,
        ]);
    }

    public function test_admin_brand_can_be_deleted(): void
    {
        $brand = Brand::create([
            'name' => 'Nokia',
            'name_bn' => 'নকিয়া',
            'slug' => 'nokia',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('brands.destroy'), [
            'hidden_id' => $brand->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('brands', [
            'id' => $brand->id,
        ]);
    }
}
