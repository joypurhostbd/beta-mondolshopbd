<?php

namespace Modules\Promotion\Domain\Contracts;

use Modules\Promotion\Domain\Entities\CampaignEntity;

interface CampaignRepositoryInterface
{
    public function findById(int $id): ?CampaignEntity;
    public function findBySlug(string $slug): ?CampaignEntity;
    public function getActive(): array;
    public function save(CampaignEntity $entity): CampaignEntity;
    public function delete(int $id): bool;
}