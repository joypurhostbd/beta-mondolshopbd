<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminCampaignAdvancedFeaturesTest extends TestCase
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

    public function test_admin_can_toggle_campaign_status_via_ajax(): void
    {
        $campaign = Campaign::create([
            'name' => 'Flash Sale 2026',
            'slug' => 'flash-sale-2026',
            'product_id' => 1,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('campaign.toggle-status'), [
                'id' => $campaign->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 0,
        ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => 0,
        ]);
    }

    public function test_admin_can_clone_campaign_as_draft(): void
    {
        $product = Product::create([
            'name' => 'Almond Nuts 500g',
            'slug' => 'almond-nuts-500g',
            'category_id' => 1,
            'product_code' => 'ALM-01',
            'old_price' => 1200,
            'new_price' => 1050,
            'purchase_price' => 800,
            'stock' => 15,
            'status' => 1,
        ]);

        $campaign = Campaign::create([
            'name' => 'Mega Offer Almonds',
            'offer_title' => 'Special Mega Discount 20%',
            'slug' => 'mega-offer-almonds',
            'product_id' => $product->id,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'special_price' => 950.00,
            'free_shipping' => 1,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('campaign.clone', $campaign->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Mega Offer Almonds (Copy)',
            'offer_title' => 'Special Mega Discount 20%',
            'product_id' => $product->id,
            'special_price' => 950.00,
            'free_shipping' => 1,
            'status' => 0, // Should be draft
        ]);
    }

    public function test_admin_can_create_campaign_with_advanced_fields(): void
    {
        $product = Product::create([
            'name' => 'Premium Honey 1kg',
            'slug' => 'premium-honey-1kg',
            'category_id' => 1,
            'product_code' => 'HON-100',
            'old_price' => 1800,
            'new_price' => 1600,
            'purchase_price' => 1100,
            'stock' => 25,
            'status' => 1,
        ]);

        $heroImage = UploadedFile::fake()->image('hero_banner.webp', 1200, 500);

        $response = $this->actingAs($this->adminUser)->post(route('campaign.store'), [
            'name' => 'New Season Pure Honey Offer',
            'offer_title' => 'প্রাকৃতিক সুন্দরবনের মধুর স্পেশাল অফার',
            'product_id' => $product->id,
            'video_url' => 'https://youtu.be/tgvXXbONtQw',
            'start_date' => now()->format('Y-m-d H:i'),
            'end_date' => now()->addDays(5)->format('Y-m-d H:i'),
            'special_price' => 1450.00,
            'free_shipping' => 1,
            'image_one' => $heroImage,
            'short_description' => '১০০% খাঁটি ও প্রাকৃতিক সুন্দরবনের মধু।',
            'description' => 'বিস্তারিত পণ্যের বর্ণনা ও গুণাগুণ।',
            'review' => 'গ্রাহকদের অভিমত',
            'status' => 1,
            'meta_title' => 'খাঁটি সুন্দরবনের মধু অফার',
            'meta_description' => 'অর্ডারে পাচ্ছেন ফ্রি ডেলিভারি সারা বাংলাদেশে',
        ]);

        $response->assertRedirect(route('campaign.index'));

        $this->assertDatabaseHas('campaigns', [
            'name' => 'New Season Pure Honey Offer',
            'offer_title' => 'প্রাকৃতিক সুন্দরবনের মধুর স্পেশাল অফার',
            'product_id' => $product->id,
            'video_url' => 'https://youtu.be/tgvXXbONtQw',
            'special_price' => 1450.00,
            'free_shipping' => 1,
            'status' => 1,
        ]);
    }
}
