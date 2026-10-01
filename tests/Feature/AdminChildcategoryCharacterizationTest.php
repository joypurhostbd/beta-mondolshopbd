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

class AdminChildcategoryCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'childcategory-list',
            'childcategory-create',
            'childcategory-edit',
            'childcategory-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_childcategory_manage_renders_with_kpi_metrics_and_hierarchy(): void
    {
        $category = Category::create([
            'name' => 'Fashion & Wear',
            'slug' => 'fashion-wear',
            'status' => 1,
            'front_view' => 1,
            'parent_id' => 0,
        ]);

        $sub = Subcategory::create([
            'subcategoryName' => 'Mens Fashion',
            'slug' => 'mens-fashion',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $child1 = Childcategory::create([
            'childcategoryName' => 'Formal Shirts',
            'slug' => 'formal-shirts',
            'subcategory_id' => $sub->id,
            'status' => 1,
        ]);

        $child2 = Childcategory::create([
            'childcategoryName' => 'Casual Jeans',
            'slug' => 'casual-jeans',
            'subcategory_id' => $sub->id,
            'status' => 0,
        ]);

        Product::create([
            'name' => 'Cotton Formal Shirt White',
            'slug' => 'cotton-formal-shirt-white',
            'category_id' => $category->id,
            'subcategory_id' => $sub->id,
            'childcategory_id' => $child1->id,
            'product_code' => 'CS-01',
            'old_price' => 2000,
            'new_price' => 1800,
            'purchase_price' => 1200,
            'stock' => 25,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('childcategories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['inactive'] === 1
                && $metrics['linked_subcategories'] === 1;
        });

        $response->assertSee('Formal Shirts');
        $response->assertSee('Casual Jeans');
        $response->assertSee('Fashion &amp; Wear', false);
        $response->assertSee('Mens Fashion');
        $response->assertSee('1 Products');
    }

    public function test_admin_childcategory_can_toggle_active_and_inactive(): void
    {
        $category = Category::create([
            'name' => 'Gadgets',
            'slug' => 'gadgets',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $sub = Subcategory::create([
            'subcategoryName' => 'Audio',
            'slug' => 'audio',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $child = Childcategory::create([
            'childcategoryName' => 'Bluetooth Speakers',
            'slug' => 'bluetooth-speakers',
            'subcategory_id' => $sub->id,
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('childcategories.inactive'), [
            'hidden_id' => $child->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('childcategories', [
            'id' => $child->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('childcategories.active'), [
            'hidden_id' => $child->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('childcategories', [
            'id' => $child->id,
            'status' => 1,
        ]);
    }

    public function test_admin_childcategory_can_be_deleted(): void
    {
        $category = Category::create([
            'name' => 'Beauty',
            'slug' => 'beauty',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $sub = Subcategory::create([
            'subcategoryName' => 'Skincare',
            'slug' => 'skincare',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $child = Childcategory::create([
            'childcategoryName' => 'Face Wash',
            'slug' => 'face-wash',
            'subcategory_id' => $sub->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('childcategories.destroy'), [
            'hidden_id' => $child->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('childcategories', [
            'id' => $child->id,
        ]);
    }
}
