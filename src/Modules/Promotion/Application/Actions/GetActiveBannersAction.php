<?php

namespace Modules\Promotion\Application\Actions;

use App\Models\Banner;
use Modules\Promotion\Application\DTOs\BannerDTO;
use Throwable;

class GetActiveBannersAction
{
    public function execute(?int $categoryId = null): array
    {
        try {
            $query = Banner::active();
            if ($categoryId) {
                $query->forCategory($categoryId);
            }
            return $query->get()->map(fn($b) => new BannerDTO(
                id: $b->id,
                link: $b->link,
                categoryId: $b->category_id,
                image: $b->image,
                isActive: (bool) $b->status
            )->toArray())->toArray();
        } catch (Throwable) {
            return [];
        }
    }
}