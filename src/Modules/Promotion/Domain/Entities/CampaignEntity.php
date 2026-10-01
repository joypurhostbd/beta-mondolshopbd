<?php

namespace Modules\Promotion\Domain\Entities;

class CampaignEntity
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public string $slug,
        public ?string $bannerImage = null,
        public ?int $productId = null,
        public ?string $description = null,
        public bool $isActive = true
    ) {}
}