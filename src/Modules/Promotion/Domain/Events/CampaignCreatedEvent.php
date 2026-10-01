<?php

namespace Modules\Promotion\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $campaignId,
        public readonly string $campaignName,
        public readonly string $slug
    ) {}
}