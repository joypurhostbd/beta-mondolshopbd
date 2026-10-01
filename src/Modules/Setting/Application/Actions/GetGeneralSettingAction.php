<?php

namespace Modules\Setting\Application\Actions;

use Modules\Setting\Application\DTOs\GeneralSettingDTO;
use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;

class GetGeneralSettingAction
{
    public function __construct(
        private SettingRepositoryInterface $repository
    ) {}

    public function execute(): ?GeneralSettingDTO
    {
        $entity = $this->repository->getGeneralSetting();
        if (!$entity) {
            return null;
        }

        return new GeneralSettingDTO(
            id: $entity->id,
            name: $entity->name,
            whiteLogo: $entity->whiteLogo,
            darkLogo: $entity->darkLogo,
            favicon: $entity->favicon,
            description: $entity->description,
            isActive: $entity->isActive
        );
    }
}