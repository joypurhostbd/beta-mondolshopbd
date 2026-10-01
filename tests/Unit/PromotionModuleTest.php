<?php

namespace Tests\Unit;

use App\Models\Banner;
use App\Models\Campaign;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Promotion\Application\Actions\CreateCampaignAction;
use Modules\Promotion\Application\Actions\SubmitReviewAction;
use Modules\Promotion\Domain\Events\CampaignCreatedEvent;
use Modules\Promotion\Domain\Events\ReviewSubmittedEvent;
use Shared\Domain\Contracts\Modules\PromotionModuleInterface;
use Tests\TestCase;

class PromotionModuleTest extends TestCase
{
    use RefreshDatabase;

    private PromotionModuleInterface $promotionModule;
    private CreateCampaignAction $createCampaignAction;
    private SubmitReviewAction $submitReviewAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionModule = $this->app->make(PromotionModuleInterface::class);
        $this->createCampaignAction = $this->app->make(CreateCampaignAction::class);
        $this->submitReviewAction = $this->app->make(SubmitReviewAction::class);

        Campaign::query()->delete();
        Banner::query()->delete();
        Review::query()->delete();

        $category = \App\Models\Category::create([
            'id' => 1,
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => 1,
        ]);

        foreach ([205, 301] as $pid) {
            $prod = new \App\Models\Product();
            $prod->id = $pid;
            $prod->name = 'Product ' . $pid;
            $prod->slug = 'product-' . $pid;
            $prod->product_code = 'PC-' . $pid;
            $prod->category_id = $category->id;
            $prod->purchase_price = 1000;
            $prod->new_price = 1200;
            $prod->old_price = 1500;
            $prod->stock = 10;
            $prod->status = 1;
            $prod->save();
        }
    }

    public function test_create_campaign_action_persists_and_dispatches_event(): void
    {
        Event::fake([CampaignCreatedEvent::class]);

        $dto = $this->createCampaignAction->execute([
            'name' => 'Eid Mega Sale 2026',
            'banner' => 'campaigns/eid-mega-sale.jpg',
            'product_id' => 101,
            'description' => 'Huge discounts on Eid collection',
            'status' => 1,
        ]);

        $this->assertNotNull($dto->id);
        $this->assertEquals('Eid Mega Sale 2026', $dto->name);
        $this->assertEquals('eid-mega-sale-2026', $dto->slug);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Eid Mega Sale 2026',
            'slug' => 'eid-mega-sale-2026',
        ]);

        Event::assertDispatched(CampaignCreatedEvent::class, function ($e) use ($dto) {
            return $e->campaignId === $dto->id && $e->slug === 'eid-mega-sale-2026';
        });
    }

    public function test_submit_review_action_persists_and_dispatches_event(): void
    {
        Event::fake([ReviewSubmittedEvent::class]);

        $dto = $this->submitReviewAction->execute([
            'product_id' => 205,
            'customer_id' => 12,
            'name' => 'Sizar Babu',
            'email' => 'sizar@joypurhost.com',
            'ratting' => 5,
            'review' => 'Excellent premium quality fabric and fast delivery!',
            'status' => 'active',
        ]);

        $this->assertNotNull($dto->id);
        $this->assertEquals(205, $dto->productId);
        $this->assertEquals(5, $dto->rating);

        $this->assertDatabaseHas('reviews', [
            'product_id' => 205,
            'ratting' => 5,
        ]);

        Event::assertDispatched(ReviewSubmittedEvent::class, function ($e) use ($dto) {
            return $e->reviewId === $dto->id && $e->productId === 205 && $e->rating === 5;
        });
    }

    public function test_promotion_service_queries_campaigns_banners_reviews(): void
    {
        // 1. Campaign
        Campaign::create([
            'name' => 'Winter Clearance',
            'slug' => 'winter-clearance',
            'image_one' => 'banner.jpg',
            'short_description' => 'Winter clearance deals',
            'description' => 'Winter clearance description',
            'review' => '1',
            'date' => '2026-09-03',
            'status' => '1',
        ]);

        $activeCampaigns = $this->promotionModule->getActiveCampaigns();
        $this->assertCount(1, $activeCampaigns);
        $this->assertEquals('Winter Clearance', $activeCampaigns[0]['name']);

        $campaign = $this->promotionModule->getCampaignBySlug('winter-clearance');
        $this->assertNotNull($campaign);
        $this->assertEquals('Winter Clearance', $campaign['name']);

        // 2. Banner
        Banner::create([
            'link' => 'https://mondolshop.com/hot-deals',
            'category_id' => 1,
            'image' => 'banners/hot-deal.jpg',
            'status' => 1,
        ]);

        $banners = $this->promotionModule->getActiveBanners(1);
        $this->assertCount(1, $banners);
        $this->assertEquals('banners/hot-deal.jpg', $banners[0]['image']);

        // 3. Review
        Review::create([
            'product_id' => 301,
            'customer_id' => 1,
            'name' => 'Satisfied Customer',
            'email' => 'customer@example.com',
            'ratting' => 5,
            'review' => 'Loved the fit!',
            'status' => 'active',
        ]);

        $reviews = $this->promotionModule->getProductReviews(301);
        $this->assertCount(1, $reviews);
        $this->assertEquals('Satisfied Customer', $reviews[0]['customer_name']);
    }
}