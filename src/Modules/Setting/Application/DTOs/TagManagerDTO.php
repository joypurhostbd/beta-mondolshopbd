<?php

namespace Modules\Setting\Application\DTOs;

class TagManagerDTO
{
    /**
     * @param array<int, array> $eventConfigs
     */
    public function __construct(
        public readonly ?int $id,
        public readonly string $code,
        public readonly int $status,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly bool $isServerSide = false,
        public readonly ?string $serverContainerUrl = null,
        public readonly ?string $measurementId = null,
        public readonly ?string $apiSecret = null,
        public readonly bool $customLoaderDomain = false,
        public readonly array $eventConfigs = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            code: trim((string) ($data['code'] ?? '')),
            status: isset($data['status']) && (int) $data['status'] === 1 ? 1 : 0,
            title: !empty($data['title']) ? trim((string) $data['title']) : null,
            description: !empty($data['description']) ? trim((string) $data['description']) : null,
            isServerSide: isset($data['is_server_side']) && (int) $data['is_server_side'] === 1,
            serverContainerUrl: !empty($data['server_container_url']) ? rtrim(trim((string) $data['server_container_url']), '/') : null,
            measurementId: !empty($data['measurement_id']) ? strtoupper(trim((string) $data['measurement_id'])) : null,
            apiSecret: !empty($data['api_secret']) ? trim((string) $data['api_secret']) : null,
            customLoaderDomain: isset($data['custom_loader_domain']) && (int) $data['custom_loader_domain'] === 1,
            eventConfigs: isset($data['events']) && is_array($data['events']) ? $data['events'] : []
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'is_server_side' => $this->isServerSide ? 1 : 0,
            'server_container_url' => $this->serverContainerUrl,
            'measurement_id' => $this->measurementId,
            'api_secret' => $this->apiSecret,
            'custom_loader_domain' => $this->customLoaderDomain ? 1 : 0,
        ];
    }
}
