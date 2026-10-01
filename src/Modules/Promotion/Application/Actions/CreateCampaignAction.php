<?php

namespace Modules\Promotion\Application\Actions;

use Illuminate\Support\Str;
use Modules\Promotion\Application\DTOs\CampaignDTO;
use Modules\Promotion\Domain\Contracts\CampaignRepositoryInterface;
use Modules\Promotion\Domain\Entities\CampaignEntity;
use Modules\Promotion\Domain\Events\CampaignCreatedEvent;

class CreateCampaignAction
{
    public function __construct(
        private CampaignRepositoryInterface $repository
    ) {}

    public function execute(array $data): CampaignDTO
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        $entity = new CampaignEntity(
            id: null,
            name: $data['name'],
            slug: $slug,
            bannerImage: $data['banner'] ?? $data['banner_image'] ?? null,
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            description: $data['description'] ?? null,
            isActive: (bool) ($data['status'] ?? true)
        );

        $saved = $this->repository->save($entity);

        event(new CampaignCreatedEvent($saved->id, $saved->name, $saved->slug));

        return new CampaignDTO(
            id: $saved->id,
            name: $saved->name,
            slug: $saved->slug,
            bannerImage: $saved->bannerImage,
            productId: $saved->productId,
            description: $saved->description,
            isActive: $saved->isActive
        );
    }
}