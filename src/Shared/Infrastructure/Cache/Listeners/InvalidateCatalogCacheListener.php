<?php

namespace Shared\Infrastructure\Cache\Listeners;

use Shared\Infrastructure\Cache\CacheKeys;
use Shared\Infrastructure\Cache\RedisCacheManager;

class InvalidateCatalogCacheListener
{
    public function __construct(
        private RedisCacheManager $cacheManager
    ) {}

    public function handle(mixed $event): void
    {
        $keysToForget = [
            CacheKeys::CATEGORIES_TREE,
            CacheKeys::HOT_DEALS,
            CacheKeys::TOP_RATED,
        ];

        if (isset($event->productId)) {
            $keysToForget[] = CacheKeys::productKey((int) $event->productId);
        }

        $this->cacheManager->forgetMultiple($keysToForget);
    }
}