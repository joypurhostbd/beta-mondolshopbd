<?php

namespace Modules\Promotion\Application\DTOs;

class BannerDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $link,
        public readonly ?int $categoryId,
        public readonly ?string $image,
        public readonly bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'link' => $this->link,
            'category_id' => $this->categoryId,
            'image' => $this->image,
            'is_active' => $this->isActive,
        ];
    }
}