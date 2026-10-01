<?php

namespace Modules\Setting\Application\DTOs;

class SocialMediaDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly ?string $icon,
        public readonly string $link,
        public readonly bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'icon' => $this->icon,
            'link' => $this->link,
            'is_active' => $this->isActive,
        ];
    }
}