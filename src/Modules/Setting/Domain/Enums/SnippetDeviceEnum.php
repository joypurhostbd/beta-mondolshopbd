<?php

namespace Modules\Setting\Domain\Enums;

enum SnippetDeviceEnum: string
{
    case ALL = 'all';
    case DESKTOP = 'desktop';
    case MOBILE = 'mobile';

    public function label(): string
    {
        return match ($this) {
            self::ALL => 'All Devices',
            self::DESKTOP => 'Desktop Only',
            self::MOBILE => 'Mobile Only',
        };
    }
}
