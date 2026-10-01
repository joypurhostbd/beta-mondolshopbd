<?php

namespace Modules\Promotion\Domain\Entities;

class BannerEntity
{
    public function __construct(
        public readonly ?int $id,
        public ?string $link,
        public ?int $categoryId,
        public ?string $image,
        public bool $isActive = true
    ) {}
}