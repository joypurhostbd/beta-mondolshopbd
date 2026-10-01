<?php

namespace Modules\Setting\Application\DTOs;

class ContactInfoDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $address,
        public readonly ?string $hotline,
        public readonly bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'hotline' => $this->hotline,
            'is_active' => $this->isActive,
        ];
    }
}