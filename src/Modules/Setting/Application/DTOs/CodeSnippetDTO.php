<?php

namespace Modules\Setting\Application\DTOs;

use Modules\Setting\Domain\Enums\SnippetDeviceEnum;
use Modules\Setting\Domain\Enums\SnippetLocationEnum;
use Modules\Setting\Domain\Enums\SnippetTypeEnum;

class CodeSnippetDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly SnippetTypeEnum $type,
        public readonly SnippetLocationEnum $location,
        public readonly string $code,
        public readonly bool $status = true,
        public readonly int $priority = 10,
        public readonly SnippetDeviceEnum $deviceTarget = SnippetDeviceEnum::ALL,
        public readonly string $targetPages = 'all',
        public readonly ?string $customPageUrls = null,
        public readonly string $authCondition = 'all',
        public readonly ?string $description = null
    ) {}

    public static function fromArray(array $data, ?int $id = null): self
    {
        return new self(
            id: $id ?? ($data['id'] ?? null),
            title: trim($data['title']),
            type: SnippetTypeEnum::tryFrom($data['type'] ?? 'html') ?? SnippetTypeEnum::HTML,
            location: SnippetLocationEnum::tryFrom($data['location'] ?? 'head') ?? SnippetLocationEnum::HEAD,
            code: (string) ($data['code'] ?? ''),
            status: isset($data['status']) ? (bool) $data['status'] : true,
            priority: isset($data['priority']) ? (int) $data['priority'] : 10,
            deviceTarget: SnippetDeviceEnum::tryFrom($data['device_target'] ?? 'all') ?? SnippetDeviceEnum::ALL,
            targetPages: $data['target_pages'] ?? 'all',
            customPageUrls: !empty($data['custom_page_urls']) ? trim($data['custom_page_urls']) : null,
            authCondition: $data['auth_condition'] ?? 'all',
            description: !empty($data['description']) ? trim($data['description']) : null
        );
    }
}
