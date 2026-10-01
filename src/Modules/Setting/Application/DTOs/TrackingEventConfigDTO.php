<?php

namespace Modules\Setting\Application\DTOs;

class TrackingEventConfigDTO
{
    public function __construct(
        public readonly string $eventKey,
        public readonly bool $isWebEnabled = true,
        public readonly bool $isServerEnabled = true,
        public readonly ?string $customEventName = null,
        public readonly array $parameters = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventKey: (string) ($data['event_key'] ?? ''),
            isWebEnabled: isset($data['is_web_enabled']) ? (bool) $data['is_web_enabled'] : true,
            isServerEnabled: isset($data['is_server_enabled']) ? (bool) $data['is_server_enabled'] : true,
            customEventName: !empty($data['custom_event_name']) ? trim((string) $data['custom_event_name']) : null,
            parameters: isset($data['parameters']) && is_array($data['parameters']) ? $data['parameters'] : []
        );
    }

    public function toArray(): array
    {
        return [
            'event_key' => $this->eventKey,
            'is_web_enabled' => $this->isWebEnabled ? 1 : 0,
            'is_server_enabled' => $this->isServerEnabled ? 1 : 0,
            'custom_event_name' => $this->customEventName,
            'parameters' => $this->parameters,
        ];
    }
}
