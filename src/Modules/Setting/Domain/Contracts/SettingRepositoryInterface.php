<?php

namespace Modules\Setting\Domain\Contracts;

use Modules\Setting\Domain\Entities\ContactInfoEntity;
use Modules\Setting\Domain\Entities\GeneralSettingEntity;
use Modules\Setting\Domain\Entities\PageEntity;

interface SettingRepositoryInterface
{
    public function getGeneralSetting(): ?GeneralSettingEntity;
    public function saveGeneralSetting(GeneralSettingEntity $entity): GeneralSettingEntity;
    public function getActiveSocialMedia(): array;
    public function getContactInfo(): ?ContactInfoEntity;
    public function findPageBySlug(string $slug): ?PageEntity;
}