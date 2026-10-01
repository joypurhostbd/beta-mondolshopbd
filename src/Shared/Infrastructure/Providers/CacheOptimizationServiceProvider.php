<?php

namespace Shared\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Domain\Events\ProductCreatedEvent;
use Modules\Catalog\Domain\Events\ProductStockUpdatedEvent;
use Modules\Catalog\Domain\Events\ProductUpdatedEvent;
use Modules\Setting\Domain\Events\SettingUpdatedEvent;
use Shared\Infrastructure\Cache\Listeners\InvalidateCatalogCacheListener;
use Shared\Infrastructure\Cache\Listeners\InvalidateSettingCacheListener;
use Shared\Infrastructure\Cache\RedisCacheManager;

class CacheOptimizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RedisCacheManager::class);
    }

    public function boot(): void
    {
        Event::listen(ProductCreatedEvent::class, InvalidateCatalogCacheListener::class);
        Event::listen(ProductUpdatedEvent::class, InvalidateCatalogCacheListener::class);
        Event::listen(ProductStockUpdatedEvent::class, InvalidateCatalogCacheListener::class);
        Event::listen(SettingUpdatedEvent::class, InvalidateSettingCacheListener::class);
    }
}