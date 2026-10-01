<?php

namespace Modules\Setting\Application\Actions;

use Modules\Setting\Application\DTOs\ContactInfoDTO;
use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;

class GetContactInfoAction
{
    public function __construct(
        private SettingRepositoryInterface $repository
    ) {}

    public function execute(): ?ContactInfoDTO
    {
        $c = $this->repository->getContactInfo();
        if (!$c) {
            return null;
        }

        return new ContactInfoDTO(
            id: $c->id,
            phone: $c->phone,
            email: $c->email,
            address: $c->address,
            hotline: $c->hotline,
            isActive: $c->isActive
        );
    }
}