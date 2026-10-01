<?php

namespace Modules\Setting\Application\Actions;

use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;

class GetSocialMediaLinksAction
{
    public function __construct(
        private SettingRepositoryInterface $repository
    ) {}

    public function execute(): array
    {
        return array_map(fn($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'icon' => $item->icon,
            'link' => $item->link,
        ], $this->repository->getActiveSocialMedia());
    }
}