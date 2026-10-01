<?php

namespace Shared\Domain\Contracts\Modules;

interface PromotionModuleInterface
{
    public function getActiveCampaigns(): array;

    public function getCampaignBySlug(string $slug): ?array;

    public function getActiveBanners(?int $categoryId = null): array;

    public function getProductReviews(int $productId): array;

    public function submitReview(array $data): array;
}