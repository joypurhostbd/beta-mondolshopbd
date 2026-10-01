<?php

namespace Shared\Domain\Contracts\Modules;

interface SettingModuleInterface
{
    public function getGeneralSetting(): ?array;

    public function updateGeneralSetting(array $data): array;

    public function getSocialMediaLinks(): array;

    public function getContactInfo(): ?array;

    public function getPageBySlug(string $slug): ?array;
}