<?php

namespace Modules\Setting\Infrastructure\Repositories;

use App\Models\Contact;
use App\Models\CreatePage;
use App\Models\GeneralSetting;
use App\Models\SocialMedia;
use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;
use Modules\Setting\Domain\Entities\ContactInfoEntity;
use Modules\Setting\Domain\Entities\GeneralSettingEntity;
use Modules\Setting\Domain\Entities\PageEntity;
use Modules\Setting\Domain\Entities\SocialMediaEntity;
use Throwable;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function getGeneralSetting(): ?GeneralSettingEntity
    {
        try {
            $setting = GeneralSetting::first();
            if (!$setting) {
                return null;
            }

            return new GeneralSettingEntity(
                id: $setting->id,
                name: $setting->name ?? 'MondolShopBD',
                whiteLogo: $setting->white_logo,
                darkLogo: $setting->dark_logo,
                favicon: $setting->favicon,
                description: $setting->description,
                isActive: (bool) ($setting->status ?? true)
            );
        } catch (Throwable) {
            return null;
        }
    }

    public function saveGeneralSetting(GeneralSettingEntity $entity): GeneralSettingEntity
    {
        $setting = $entity->id ? GeneralSetting::find($entity->id) : GeneralSetting::first();
        if (!$setting) {
            $setting = new GeneralSetting();
        }

        $setting->name = $entity->name;
        $setting->white_logo = $entity->whiteLogo;
        $setting->dark_logo = $entity->darkLogo;
        $setting->favicon = $entity->favicon;
        $setting->description = $entity->description;
        $setting->status = $entity->isActive ? 1 : 0;
        $setting->save();

        return new GeneralSettingEntity(
            id: $setting->id,
            name: $setting->name,
            whiteLogo: $setting->white_logo,
            darkLogo: $setting->dark_logo,
            favicon: $setting->favicon,
            description: $setting->description,
            isActive: (bool) $setting->status
        );
    }

    public function getActiveSocialMedia(): array
    {
        try {
            return SocialMedia::where('status', 1)
                ->get()
                ->map(fn($item) => new SocialMediaEntity(
                    id: $item->id,
                    title: $item->title ?? '',
                    icon: $item->icon,
                    link: $item->link ?? '#',
                    isActive: (bool) $item->status
                ))
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    public function getContactInfo(): ?ContactInfoEntity
    {
        try {
            $c = Contact::first();
            if (!$c) {
                return null;
            }

            return new ContactInfoEntity(
                id: $c->id,
                phone: $c->phone,
                email: $c->email,
                address: $c->address,
                hotline: $c->hotline,
                isActive: (bool) ($c->status ?? true)
            );
        } catch (Throwable) {
            return null;
        }
    }

    public function findPageBySlug(string $slug): ?PageEntity
    {
        try {
            $page = CreatePage::where('slug', $slug)->first();
            if (!$page) {
                return null;
            }

            return new PageEntity(
                id: $page->id,
                title: $page->title ?? '',
                slug: $page->slug ?? $slug,
                description: $page->description,
                isActive: (bool) ($page->status ?? true)
            );
        } catch (Throwable) {
            return null;
        }
    }
}