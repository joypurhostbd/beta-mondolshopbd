<?php

namespace Modules\Setting\Application\ViewModels;

use Illuminate\Database\Eloquent\Collection;
use Modules\Setting\Domain\Enums\TrackingEventEnum;

class TagManagerIndexViewModel
{
    /**
     * @param Collection $tags
     */
    public function __construct(
        public readonly Collection $tags
    ) {}

    public function total(): int
    {
        return $this->tags->count();
    }

    public function active(): int
    {
        return $this->tags->where('status', 1)->count();
    }

    public function inactive(): int
    {
        return $this->tags->where('status', 0)->count();
    }

    public function serverSideCount(): int
    {
        return $this->tags->where('status', 1)->where('is_server_side', 1)->count();
    }

    public function latestActiveCode(): string
    {
        $firstActive = $this->tags->where('status', 1)->first();
        return $firstActive?->code ?? 'Not Configured';
    }

    public function activeTagsList(): array
    {
        return $this->tags->where('status', 1)->pluck('code')->all();
    }

    public function hasMultipleActive(): bool
    {
        return $this->active() > 1;
    }

    public function isEmpty(): bool
    {
        return $this->tags->isEmpty();
    }

    public function toViewData(): array
    {
        return [
            'data' => $this->tags,
            'total_tags' => $this->total(),
            'active_tags' => $this->active(),
            'inactive_tags' => $this->inactive(),
            'server_side_count' => $this->serverSideCount(),
            'latest_tag' => $this->latestActiveCode(),
            'has_multiple_active' => $this->hasMultipleActive(),
            'active_tags_list' => $this->activeTagsList(),
            'is_empty' => $this->isEmpty(),
            'standard_events' => TrackingEventEnum::cases(),
        ];
    }
}
