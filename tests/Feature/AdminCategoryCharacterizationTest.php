<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminCategoryCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_category_manage_renders_with_kpi_metrics_and_counts(): void
    {
        $cat1 = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 1,
            'front_view' => 1,
            'parent_id' => 0,
        ]);

        $cat2 = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => 0,
            'front_view' => 0,
            'parent_id' => 0,
        ]);

        Subcategory::create([
            'subcategoryName' => 'Smartphones',
            'slug' => 'smartphones',
            'category_id' => $cat1->id,
            'status' => 1,
        ]);

        Product::create([
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'category_id' => $cat1->id,
            'product_code' => 'IP-15P',
            'old_price' => 150000,
            'new_price' => 140000,
            'purchase_price' => 120000,
            'stock' => 10,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['inactive'] === 1
                && $metrics['front_view'] === 1;
        });

        $response->assertSee('Electronics');
        $response->assertSee('Fashion');
        $response->assertSee('1 Subs');
        $response->assertSee('1 Products');
    }

    public function test_admin_category_can_toggle_active_and_inactive(): void
    {
        $cat = Category::create([
            'name' => 'Home & Kitchen',
            'slug' => 'home-kitchen',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('categories.inactive'), [
            'hidden_id' => $cat->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $cat->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('categories.active'), [
            'hidden_id' => $cat->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $cat->id,
            'status' => 1,
        ]);
    }

    public function test_admin_category_can_be_deleted(): void
    {
        $cat = Category::create([
            'name' => 'To Be Deleted',
            'slug' => 'to-be-deleted',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('categories.destroy'), [
            'hidden_id' => $cat->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', [
            'id' => $cat->id,
        ]);
    }

    public function test_admin_can_store_category_without_image(): void
    {
        $payload = [
            'name'             => 'Sports & Outdoors',
            'status'           => '1',
            'front_view'       => '1',
            'meta_title'       => 'Best Sports Gear',
            'meta_description' => 'Browse high quality sports equipment',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('categories.store'), $payload);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name'             => 'Sports & Outdoors',
            'slug'             => 'sports-outdoors',
            'status'           => 1,
            'front_view'       => 1,
            'meta_title'       => 'Best Sports Gear',
            'meta_description' => 'Browse high quality sports equipment',
            'image'            => 'uploads/category/default.png',
        ]);
    }

    public function test_admin_can_store_category_with_unchecked_status(): void
    {
        $payload = [
            'name' => 'Winter Special',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('categories.store'), $payload);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name'   => 'Winter Special',
            'slug'   => 'winter-special',
            'status' => 0,
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $cat = Category::create([
            'name'   => 'Old Category',
            'slug'   => 'old-category',
            'status' => 1,
            'image'  => 'uploads/category/default.png',
        ]);

        $payload = [
            'id'               => $cat->id,
            'name'             => 'Updated Category Name',
            'status'           => '1',
            'meta_title'       => 'Updated SEO Title',
            'meta_description' => 'Updated Description',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('categories.update'), $payload);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id'         => $cat->id,
            'name'       => 'Updated Category Name',
            'slug'       => 'updated-category-name',
            'meta_title' => 'Updated SEO Title',
        ]);
    }

    public function test_admin_can_store_category_with_uploaded_image(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->image('category_sample.jpg', 600, 400);

        $payload = [
            'name'   => 'Gadgets & Tech',
            'status' => '1',
            'image'  => $file,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('categories.store'), $payload);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name'   => 'Gadgets & Tech',
            'slug'   => 'gadgets-tech',
            'status' => 1,
        ]);

        $created = Category::where('slug', 'gadgets-tech')->first();
        $this->assertNotNull($created);
        $this->assertStringStartsWith('uploads/category/', $created->image);
        $this->assertFileExists(public_path($created->image));

        // Clean up test file
        if (\Illuminate\Support\Facades\File::exists(public_path($created->image))) {
            \Illuminate\Support\Facades\File::delete(public_path($created->image));
        }
    }
}
