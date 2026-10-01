<?php

namespace Modules\Setting\Domain\Entities;

class SocialMediaEntity
{
    public function __construct(
        public readonly ?int $id,
        public string $title,
        public ?string $icon,
        public string $link,
        public bool $isActive = true
    ) {}
}