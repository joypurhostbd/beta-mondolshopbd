<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCampaignCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'campaign-list',
            'campaign-create',
            'campaign-edit',
            'campaign-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_can_view_campaign_create_page_with_products(): void
    {
        $product = Product::create([
            'name' => 'Organic Honey 500g',
            'slug' => 'organic-honey-500g',
            'category_id' => 1,
            'product_code' => 'HON-01',
            'old_price' => 850,
            'new_price' => 750,
            'purchase_price' => 500,
            'stock' => 50,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('campaign.create'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertSee('Create Landing Page Campaign');
        $response->assertSee('Organic Honey 500g');
        $response->assertSee('HON-01');
    }

    public function test_admin_can_store_campaign_with_images(): void
    {
        $product = Product::create([
            'name' => 'Cashew Nut 1kg',
            'slug' => 'cashew-nut-1kg',
            'category_id' => 1,
            'product_code' => 'CN-01',
            'old_price' => 1500,
            'new_price' => 1350,
            'purchase_price' => 1000,
            'stock' => 30,
            'status' => 1,
        ]);

        $heroImage = UploadedFile::fake()->image('hero.jpg', 800, 600);
        $featureImage2 = UploadedFile::fake()->image('feature2.jpg', 600, 400);

        $response = $this->actingAs($this->adminUser)->post(route('campaign.store'), [
            'name' => 'Cashew Nut Mega Offer',
            'product_id' => $product->id,
            'image_one' => $heroImage,
            'image_two' => $featureImage2,
            'short_description' => 'Premium quality cashew nuts directly imported.',
            'description' => 'Detailed benefits of cashew nuts and health value.',
            'review' => 'What our customers say',
            'status' => 1,
        ]);

        $response->assertRedirect(route('campaign.index'));

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Cashew Nut Mega Offer',
            'slug' => 'cashew-nut-mega-offer',
            'product_id' => $product->id,
            'status' => 1,
        ]);

        $campaign = Campaign::where('slug', 'cashew-nut-mega-offer')->first();
        $this->assertNotNull($campaign);
        $this->assertNotEmpty($campaign->image_one);
        $this->assertNotEmpty($campaign->image_two);
    }

    public function test_admin_can_toggle_campaign_status(): void
    {
        $campaign = Campaign::create([
            'name' => 'Winter Special Campaign',
            'slug' => 'winter-special-campaign',
            'product_id' => 1,
            'short_description' => 'Short info',
            'description' => 'Full info',
            'image_one' => 'uploads/campaign/test-1.webp',
            'review' => 'Reviews',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('campaign.inactive'), [
            'hidden_id' => $campaign->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('campaign.active'), [
            'hidden_id' => $campaign->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => 1,
        ]);
    }

    public function test_admin_can_view_campaign_index_with_metrics_and_product_details(): void
    {
        $product = Product::create([
            'name' => 'Natural Honey',
            'slug' => 'natural-honey',
            'category_id' => 1,
            'product_code' => 'NH-01',
            'old_price' => 900,
            'new_price' => 780,
            'purchase_price' => 500,
            'stock' => 20,
            'status' => 1,
        ]);

        $campaign = Campaign::create([
            'name' => 'Honey Special Campaign',
            'slug' => 'honey-special-campaign',
            'product_id' => $product->id,
            'short_description' => 'Pure honey highlights',
            'description' => 'Detailed honey description',
            'image_one' => 'uploads/campaign/hero.webp',
            'review' => 'Reviews',
            'status' => 1,
        ]);

        CampaignReview::create([
            'campaign_id' => $campaign->id,
            'image' => 'uploads/campaign/review-1.webp',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('campaign.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 1
                && $metrics['active'] === 1
                && $metrics['inactive'] === 0
                && $metrics['reviews_count'] === 1;
        });

        $response->assertSee('Honey Special Campaign');
        $response->assertSee('Natural Honey');
        $response->assertSee('1 Images');
    }

    public function test_superadmin_role_can_access_campaign_routes_without_direct_permissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminUserWithoutDirectPerms = User::factory()->create(['status' => 1]);
        $adminUserWithoutDirectPerms->assignRole($role);

        $response = $this->actingAs($adminUserWithoutDirectPerms)->get(route('campaign.create'));
        $response->assertStatus(200);
    }
}
