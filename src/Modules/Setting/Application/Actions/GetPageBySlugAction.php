<?php

namespace Modules\Setting\Application\Actions;

use Modules\Setting\Application\DTOs\PageDTO;
use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;

class GetPageBySlugAction
{
    public function __construct(
        private SettingRepositoryInterface $repository
    ) {}

    public function execute(string $slug): ?PageDTO
    {
        $page = $this->repository->findPageBySlug($slug);
        if (!$page) {
            return null;
        }

        return new PageDTO(
            id: $page->id,
            title: $page->title,
            slug: $page->slug,
            description: $page->description,
            isActive: $page->isActive
        );
    }
}