<?php

namespace Modules\Promotion\Application\DTOs;

class CampaignDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $bannerImage = null,
        public readonly ?int $productId = null,
        public readonly ?string $description = null,
        public readonly bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'banner_image' => $this->bannerImage,
            'product_id' => $this->productId,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}