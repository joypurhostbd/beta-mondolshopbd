<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Childcategory;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSubcategoryCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'subcategory-list',
            'subcategory-create',
            'subcategory-edit',
            'subcategory-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_subcategory_manage_renders_with_kpi_metrics_and_depth(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 1,
            'front_view' => 1,
            'parent_id' => 0,
        ]);

        $sub1 = Subcategory::create([
            'subcategoryName' => 'Smartphones',
            'slug' => 'smartphones',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $sub2 = Subcategory::create([
            'subcategoryName' => 'Laptops',
            'slug' => 'laptops',
            'category_id' => $category->id,
            'status' => 0,
        ]);

        Childcategory::create([
            'childcategoryName' => '5G Phones',
            'slug' => '5g-phones',
            'category_id' => $category->id,
            'subcategory_id' => $sub1->id,
            'status' => 1,
        ]);

        Product::create([
            'name' => 'Flagship Phone',
            'slug' => 'flagship-phone',
            'category_id' => $category->id,
            'subcategory_id' => $sub1->id,
            'product_code' => 'FP-01',
            'old_price' => 100000,
            'new_price' => 95000,
            'purchase_price' => 85000,
            'stock' => 15,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('subcategories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['inactive'] === 1
                && $metrics['parent_categories'] === 1;
        });

        $response->assertSee('Smartphones');
        $response->assertSee('Laptops');
        $response->assertSee('1 Child');
        $response->assertSee('1 Products');
    }

    public function test_admin_subcategory_can_toggle_active_and_inactive(): void
    {
        $category = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $sub = Subcategory::create([
            'subcategoryName' => 'Men Shirt',
            'slug' => 'men-shirt',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('subcategories.inactive'), [
            'hidden_id' => $sub->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('subcategories', [
            'id' => $sub->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('subcategories.active'), [
            'hidden_id' => $sub->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('subcategories', [
            'id' => $sub->id,
            'status' => 1,
        ]);
    }

    public function test_admin_subcategory_can_be_deleted(): void
    {
        $category = Category::create([
            'name' => 'Temporary',
            'slug' => 'temporary',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $sub = Subcategory::create([
            'subcategoryName' => 'Delete Sub',
            'slug' => 'delete-sub',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('subcategories.destroy'), [
            'hidden_id' => $sub->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('subcategories', [
            'id' => $sub->id,
        ]);
    }
}
