<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminBannerCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private BannerCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'banner-list',
            'banner-create',
            'banner-edit',
            'banner-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);

        $this->category = BannerCategory::create([
            'name' => 'Home Slider',
            'status' => 1,
        ]);
    }

    public function test_guest_cannot_access_banners(): void
    {
        $response = $this->get(route('banners.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('banners.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_banner_index_with_kpis(): void
    {
        $banner1 = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/promo-1',
            'image' => 'uploads/banner/test1.jpg',
            'status' => 1,
        ]);

        $banner2 = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/promo-2',
            'image' => 'uploads/banner/test2.jpg',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('banners.index'));

        $response->assertStatus(200);
        $response->assertSee('Banner Configuration');
        $response->assertSee('Total Banners');
        $response->assertSee('Active Banners');
        $response->assertSee('Inactive Banners');
        $response->assertSee('Categories');
        $response->assertSee('Home Slider');
        $response->assertSee('https://example.com/promo-1');
        $response->assertSee('https://example.com/promo-2');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('banners.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Banner');
        $response->assertSee('Banner Category');
        $response->assertSee('Target Link');
        $response->assertSee('Banner Image');
    }

    public function test_admin_can_store_banner(): void
    {
        $file = UploadedFile::fake()->image('banner_sample.jpg', 600, 300);

        $payload = [
            'category_id' => $this->category->id,
            'link' => 'https://example.com/festive-sale',
            'image' => $file,
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banners.store'), $payload);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseHas('banners', [
            'category_id' => $this->category->id,
            'link' => 'https://example.com/festive-sale',
            'status' => 1,
        ]);

        $created = Banner::where('link', 'https://example.com/festive-sale')->first();
        $this->assertNotNull($created);
        $this->assertNotNull($created->image);

        if (File::exists(public_path($created->image))) {
            File::delete(public_path($created->image));
        }
    }

    public function test_store_validation_requires_category_link_and_image(): void
    {
        $payload = [
            'category_id' => '',
            'link' => '',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banners.store'), $payload);

        $response->assertSessionHasErrors(['category_id', 'link', 'image']);
    }

    public function test_admin_can_view_edit_page(): void
    {
        $banner = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/edit-me',
            'image' => 'uploads/banner/sample.png',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('banners.edit', $banner->id));

        $response->assertStatus(200);
        $response->assertSee('Edit Banner');
        $response->assertSee('https://example.com/edit-me');
    }

    public function test_admin_can_update_banner_without_new_image(): void
    {
        $banner = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/initial-link',
            'image' => 'uploads/banner/original.png',
            'status' => 1,
        ]);

        $payload = [
            'id' => $banner->id,
            'category_id' => $this->category->id,
            'link' => 'https://example.com/updated-link',
            'status' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banners.update'), $payload);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'link' => 'https://example.com/updated-link',
            'image' => 'uploads/banner/original.png',
            'status' => 0,
        ]);
    }

    public function test_admin_can_toggle_banner_status(): void
    {
        $banner = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/toggle',
            'image' => 'uploads/banner/toggle.jpg',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('banners.index'))
            ->post(route('banners.inactive'), [
                'hidden_id' => $banner->id,
            ]);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('banners.index'))
            ->post(route('banners.active'), [
                'hidden_id' => $banner->id,
            ]);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'status' => 1,
        ]);
    }

    public function test_admin_can_destroy_banner(): void
    {
        $banner = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/destroy',
            'image' => 'uploads/banner/destroy.jpg',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('banners.index'))
            ->post(route('banners.destroy'), [
                'hidden_id' => $banner->id,
            ]);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseMissing('banners', [
            'id' => $banner->id,
        ]);
    }

    public function test_admin_can_store_large_banner_up_to_5mb(): void
    {
        $file = UploadedFile::fake()->image('large_banner.jpg', 1920, 600)->size(3500);

        $payload = [
            'category_id' => $this->category->id,
            'link' => 'https://example.com/large-banner',
            'image' => $file,
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('banners.store'), $payload);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseHas('banners', [
            'category_id' => $this->category->id,
            'link' => 'https://example.com/large-banner',
            'status' => 1,
        ]);

        $created = Banner::where('link', 'https://example.com/large-banner')->first();
        $this->assertNotNull($created);
        if (File::exists(public_path($created->image))) {
            File::delete(public_path($created->image));
        }
    }

    public function test_storefront_displays_dynamic_banners_and_ads(): void
    {
        $bottomCat = BannerCategory::create(['name' => 'Slider Bottom Ads (425X212px)', 'status' => 1]);
        $footerCat = BannerCategory::create(['name' => 'Footer Top Ads', 'status' => 1]);

        $sliderBanner = Banner::create([
            'category_id' => $this->category->id,
            'link' => 'https://example.com/slider-link',
            'image' => 'uploads/banner/slider.jpg',
            'status' => 1,
        ]);

        $bottomBanner = Banner::create([
            'category_id' => $bottomCat->id,
            'link' => 'https://example.com/bottom-link',
            'image' => 'uploads/banner/bottom.jpg',
            'status' => 1,
        ]);

        $footerBanner = Banner::create([
            'category_id' => $footerCat->id,
            'link' => 'https://example.com/footer-link',
            'image' => 'uploads/banner/footer.jpg',
            'status' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('uploads/banner/slider.jpg');
        $response->assertSee('uploads/banner/bottom.jpg');
        $response->assertSee('uploads/banner/footer.jpg');
    }
}