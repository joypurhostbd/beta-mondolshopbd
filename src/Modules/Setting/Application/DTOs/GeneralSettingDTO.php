<?php

namespace Modules\Setting\Application\DTOs;

class GeneralSettingDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $whiteLogo = null,
        public readonly ?string $darkLogo = null,
        public readonly ?string $favicon = null,
        public readonly ?string $description = null,
        public readonly bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'white_logo' => $this->whiteLogo,
            'dark_logo' => $this->darkLogo,
            'favicon' => $this->favicon,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}