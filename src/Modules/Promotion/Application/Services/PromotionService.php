<?php

namespace Modules\Promotion\Application\Services;

use Modules\Promotion\Application\Actions\CreateCampaignAction;
use Modules\Promotion\Application\Actions\GetActiveBannersAction;
use Modules\Promotion\Application\Actions\SubmitReviewAction;
use Modules\Promotion\Domain\Contracts\CampaignRepositoryInterface;
use Modules\Promotion\Domain\Contracts\ReviewRepositoryInterface;
use Shared\Domain\Contracts\Modules\PromotionModuleInterface;

class PromotionService implements PromotionModuleInterface
{
    public function __construct(
        private CampaignRepositoryInterface $campaignRepository,
        private ReviewRepositoryInterface $reviewRepository,
        private CreateCampaignAction $createCampaignAction,
        private SubmitReviewAction $submitReviewAction,
        private GetActiveBannersAction $getActiveBannersAction
    ) {}

    public function getActiveCampaigns(): array
    {
        return array_map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'banner_image' => $c->bannerImage,
            'product_id' => $c->productId,
            'description' => $c->description,
        ], $this->campaignRepository->getActive());
    }

    public function getCampaignBySlug(string $slug): ?array
    {
        $c = $this->campaignRepository->findBySlug($slug);
        if (!$c) {
            return null;
        }

        return [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'banner_image' => $c->bannerImage,
            'product_id' => $c->productId,
            'description' => $c->description,
        ];
    }

    public function getActiveBanners(?int $categoryId = null): array
    {
        return $this->getActiveBannersAction->execute($categoryId);
    }

    public function getProductReviews(int $productId): array
    {
        return array_map(fn($r) => [
            'id' => $r->id,
            'product_id' => $r->productId,
            'customer_name' => $r->customerName,
            'rating' => $r->rating,
            'review_text' => $r->reviewText,
        ], $this->reviewRepository->findByProduct($productId));
    }

    public function submitReview(array $data): array
    {
        $dto = $this->submitReviewAction->execute($data);
        return $dto->toArray();
    }
}