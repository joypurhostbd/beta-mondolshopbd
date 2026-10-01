<?php

namespace Shared\Infrastructure\Cache\Listeners;

use Shared\Infrastructure\Cache\CacheKeys;
use Shared\Infrastructure\Cache\RedisCacheManager;

class InvalidateSettingCacheListener
{
    public function __construct(
        private RedisCacheManager $cacheManager
    ) {}

    public function handle(mixed $event): void
    {
        $keysToForget = [
            CacheKeys::GENERAL_SETTINGS,
            CacheKeys::SOCIAL_MEDIA,
            CacheKeys::CONTACT_INFO,
        ];

        $this->cacheManager->forgetMultiple($keysToForget);
    }
}