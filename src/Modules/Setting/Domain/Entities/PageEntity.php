<?php

namespace Modules\Setting\Domain\Entities;

class PageEntity
{
    public function __construct(
        public readonly ?int $id,
        public string $title,
        public string $slug,
        public ?string $description,
        public bool $isActive = true
    ) {}
}