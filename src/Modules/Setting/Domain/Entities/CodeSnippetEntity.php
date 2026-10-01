<?php

namespace Modules\Setting\Domain\Entities;

use Modules\Setting\Domain\Enums\SnippetDeviceEnum;
use Modules\Setting\Domain\Enums\SnippetLocationEnum;
use Modules\Setting\Domain\Enums\SnippetTypeEnum;

class CodeSnippetEntity
{
    public function __construct(
        public readonly ?int $id,
        public string $title,
        public SnippetTypeEnum $type,
        public SnippetLocationEnum $location,
        public string $code,
        public bool $status = true,
        public int $priority = 10,
        public SnippetDeviceEnum $deviceTarget = SnippetDeviceEnum::ALL,
        public string $targetPages = 'all',
        public ?string $customPageUrls = null,
        public string $authCondition = 'all',
        public ?string $description = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {}
}
