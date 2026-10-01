<?php

namespace Modules\Setting\Application\Actions;

use Modules\Setting\Application\DTOs\GeneralSettingDTO;
use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;
use Modules\Setting\Domain\Entities\GeneralSettingEntity;
use Modules\Setting\Domain\Events\SettingUpdatedEvent;

class UpdateGeneralSettingAction
{
    public function __construct(
        private SettingRepositoryInterface $repository
    ) {}

    public function execute(array $data): GeneralSettingDTO
    {
        $existing = $this->repository->getGeneralSetting();

        $entity = new GeneralSettingEntity(
            id: $existing?->id,
            name: $data['name'] ?? $existing?->name ?? 'MondolShopBD',
            whiteLogo: $data['white_logo'] ?? $existing?->whiteLogo,
            darkLogo: $data['dark_logo'] ?? $existing?->darkLogo,
            favicon: $data['favicon'] ?? $existing?->favicon,
            description: $data['description'] ?? $existing?->description,
            isActive: isset($data['status']) ? (bool) $data['status'] : ($existing?->isActive ?? true)
        );

        $saved = $this->repository->saveGeneralSetting($entity);

        event(new SettingUpdatedEvent('general', $data));

        return new GeneralSettingDTO(
            id: $saved->id,
            name: $saved->name,
            whiteLogo: $saved->whiteLogo,
            darkLogo: $saved->darkLogo,
            favicon: $saved->favicon,
            description: $saved->description,
            isActive: $saved->isActive
        );
    }
}