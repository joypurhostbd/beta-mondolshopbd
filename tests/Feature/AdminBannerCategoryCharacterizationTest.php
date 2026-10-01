<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminBannerCategoryCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'banner-category-list',
            'banner-category-create',
            'banner-category-edit',
            'banner-category-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_cannot_access_banner_category(): void
    {
        $response = $this->get(route('banner_category.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('banner_category.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_banner_category_index_with_kpis(): void
    {
        $cat1 = BannerCategory::create([
            'name' => 'Home Main Slider',
            'status' => 1,
        ]);

        $cat2 = BannerCategory::create([
            'name' => 'Sidebar Promotion',
            'status' => 0,
        ]);

        Banner::create([
            'link' => 'https://example.com/promo1',
            'category_id' => $cat1->id,
            'image' => 'public/uploads/banner/sample1.jpg',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('banner_category.index'));

        $response->assertStatus(200);
        $response->assertSee('Banner Category Configuration');
        $response->assertSee('Total Categories');
        $response->assertSee('Active Categories');
        $response->assertSee('Inactive Categories');
        $response->assertSee('Total Banners');
        $response->assertSee('Home Main Slider');
        $response->assertSee('Sidebar Promotion');
        $response->assertSee('1 Banners');
        $response->assertDontSee(route('banner_category.destroy'));
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('banner_category.create'));
        $response->assertStatus(200);
        $response->assertSee('Create Banner Category');
        $response->assertSee('Category Name');
    }

    public function test_admin_can_store_banner_category(): void
    {
        $payload = [
            'name' => 'Footer Promo Banners',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banner_category.store'), $payload);

        $response->assertRedirect(route('banner_category.index'));
        $this->assertDatabaseHas('banner_categories', [
            'name' => 'Footer Promo Banners',
            'status' => 1,
        ]);
    }

    public function test_store_validation_requires_name(): void
    {
        $payload = [
            'name' => '',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banner_category.store'), $payload);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_view_edit_page(): void
    {
        $category = BannerCategory::create([
            'name' => 'Popup Banner',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('banner_category.edit', $category->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Banner Category');
        $response->assertSee('Popup Banner');
    }

    public function test_admin_can_update_banner_category(): void
    {
        $category = BannerCategory::create([
            'name' => 'Old Category Name',
            'status' => 1,
        ]);

        $payload = [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'status' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banner_category.update'), $payload);

        $response->assertRedirect(route('banner_category.index'));
        $this->assertDatabaseHas('banner_categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'status' => 0,
        ]);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $category = BannerCategory::create([
            'name' => 'Toggle Category',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('banner_category.index'))
            ->post(route('banner_category.inactive'), [
                'hidden_id' => $category->id,
            ]);

        $response->assertRedirect(route('banner_category.index'));
        $this->assertDatabaseHas('banner_categories', [
            'id' => $category->id,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('banner_category.index'))
            ->post(route('banner_category.active'), [
                'hidden_id' => $category->id,
            ]);

        $response->assertRedirect(route('banner_category.index'));
        $this->assertDatabaseHas('banner_categories', [
            'id' => $category->id,
            'status' => 1,
        ]);
    }

    public function test_banner_category_deletion_is_disabled(): void
    {
        $category = BannerCategory::create([
            'name' => 'Delete Category',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('banner_category.index'))
            ->post(route('banner_category.destroy'), [
                'hidden_id' => $category->id,
            ]);

        $response->assertRedirect(route('banner_category.index'));
        $this->assertDatabaseHas('banner_categories', [
            'id' => $category->id,
            'name' => 'Delete Category',
        ]);
    }
}