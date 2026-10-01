<?php

namespace Modules\Setting\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $settingType,
        public readonly array $data
    ) {}
}