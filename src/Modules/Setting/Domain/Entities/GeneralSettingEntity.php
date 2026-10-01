<?php

namespace Modules\Setting\Domain\Entities;

class GeneralSettingEntity
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public ?string $whiteLogo = null,
        public ?string $darkLogo = null,
        public ?string $favicon = null,
        public ?string $description = null,
        public bool $isActive = true
    ) {}
}