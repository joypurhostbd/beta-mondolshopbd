<?php

namespace Modules\Setting\Application\Services;

use Modules\Setting\Application\Actions\GetContactInfoAction;
use Modules\Setting\Application\Actions\GetGeneralSettingAction;
use Modules\Setting\Application\Actions\GetPageBySlugAction;
use Modules\Setting\Application\Actions\GetSocialMediaLinksAction;
use Modules\Setting\Application\Actions\UpdateGeneralSettingAction;
use Shared\Domain\Contracts\Modules\SettingModuleInterface;

class SettingService implements SettingModuleInterface
{
    public function __construct(
        private GetGeneralSettingAction $getGeneralSettingAction,
        private UpdateGeneralSettingAction $updateGeneralSettingAction,
        private GetSocialMediaLinksAction $getSocialMediaLinksAction,
        private GetContactInfoAction $getContactInfoAction,
        private GetPageBySlugAction $getPageBySlugAction
    ) {}

    public function getGeneralSetting(): ?array
    {
        $dto = $this->getGeneralSettingAction->execute();
        return $dto?->toArray();
    }

    public function updateGeneralSetting(array $data): array
    {
        $dto = $this->updateGeneralSettingAction->execute($data);
        return $dto->toArray();
    }

    public function getSocialMediaLinks(): array
    {
        return $this->getSocialMediaLinksAction->execute();
    }

    public function getContactInfo(): ?array
    {
        $dto = $this->getContactInfoAction->execute();
        return $dto?->toArray();
    }

    public function getPageBySlug(string $slug): ?array
    {
        $dto = $this->getPageBySlugAction->execute($slug);
        return $dto?->toArray();
    }
}