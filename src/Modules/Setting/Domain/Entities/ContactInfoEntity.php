<?php

namespace Modules\Setting\Domain\Entities;

class ContactInfoEntity
{
    public function __construct(
        public readonly ?int $id,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $hotline,
        public bool $isActive = true
    ) {}
}