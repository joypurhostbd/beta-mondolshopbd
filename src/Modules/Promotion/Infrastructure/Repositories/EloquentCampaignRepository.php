<?php

namespace Modules\Promotion\Infrastructure\Repositories;

use App\Models\Campaign;
use Modules\Promotion\Domain\Contracts\CampaignRepositoryInterface;
use Modules\Promotion\Domain\Entities\CampaignEntity;
use Throwable;

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    public function findById(int $id): ?CampaignEntity
    {
        $c = Campaign::find($id);
        return $c ? $this->toEntity($c) : null;
    }

    public function findBySlug(string $slug): ?CampaignEntity
    {
        $c = Campaign::where('slug', $slug)->first();
        return $c ? $this->toEntity($c) : null;
    }

    public function getActive(): array
    {
        try {
            return Campaign::where('status', 1)
                ->get()
                ->map(fn($c) => $this->toEntity($c))
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    public function save(CampaignEntity $entity): CampaignEntity
    {
        $c = $entity->id ? Campaign::find($entity->id) : new Campaign();
        if (!$c) {
            $c = new Campaign();
        }

        $c->name = $entity->name;
        $c->slug = $entity->slug;
        $c->image_one = $entity->bannerImage ?? 'default.jpg';
        $c->product_id = $entity->productId;
        $c->description = $entity->description ?? '';
        $c->short_description = $entity->description ?? '';
        $c->review = '1';
        $c->date = date('Y-m-d');
        $c->status = $entity->isActive ? '1' : '0';
        $c->save();

        return $this->toEntity($c);
    }

    public function delete(int $id): bool
    {
        $c = Campaign::find($id);
        return $c ? (bool) $c->delete() : false;
    }

    private function toEntity(Campaign $c): CampaignEntity
    {
        return new CampaignEntity(
            id: $c->id,
            name: $c->name,
            slug: $c->slug,
            bannerImage: $c->image_one,
            productId: $c->product_id ? (int) $c->product_id : null,
            description: $c->description,
            isActive: (bool) ($c->status == '1' || $c->status == 1)
        );
    }
}