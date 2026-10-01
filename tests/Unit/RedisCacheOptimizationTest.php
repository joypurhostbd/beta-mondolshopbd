<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Modules\Catalog\Domain\Events\ProductUpdatedEvent;
use Modules\Setting\Domain\Events\SettingUpdatedEvent;
use Shared\Infrastructure\Cache\CacheKeys;
use Shared\Infrastructure\Cache\Listeners\InvalidateCatalogCacheListener;
use Shared\Infrastructure\Cache\Listeners\InvalidateSettingCacheListener;
use Shared\Infrastructure\Cache\RedisCacheManager;
use Tests\TestCase;

class RedisCacheOptimizationTest extends TestCase
{
    private RedisCacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = $this->app->make(RedisCacheManager::class);
        Cache::flush();
    }

    public function test_cache_manager_remember_and_retrieve(): void
    {
        $counter = 0;
        $getter = function () use (&$counter) {
            $counter++;
            return ['tree' => 'Electronics -> Mobiles'];
        };

        $val1 = $this->cacheManager->remember(CacheKeys::CATEGORIES_TREE, 60, $getter);
        $this->assertEquals(['tree' => 'Electronics -> Mobiles'], $val1);
        $this->assertEquals(1, $counter);

        // Second call should return cached value without executing callback
        $val2 = $this->cacheManager->remember(CacheKeys::CATEGORIES_TREE, 60, $getter);
        $this->assertEquals(['tree' => 'Electronics -> Mobiles'], $val2);
        $this->assertEquals(1, $counter);
    }

    public function test_invalidate_catalog_cache_listener(): void
    {
        $this->cacheManager->put(CacheKeys::CATEGORIES_TREE, ['categories'], 300);
        $this->cacheManager->put(CacheKeys::HOT_DEALS, ['products'], 300);
        $this->cacheManager->put(CacheKeys::productKey(55), ['product_55'], 300);

        $this->assertNotNull($this->cacheManager->get(CacheKeys::CATEGORIES_TREE));
        $this->assertNotNull($this->cacheManager->get(CacheKeys::HOT_DEALS));
        $this->assertNotNull($this->cacheManager->get(CacheKeys::productKey(55)));

        $listener = $this->app->make(InvalidateCatalogCacheListener::class);
        $event = new ProductUpdatedEvent(55, ['price' => 500.0]);
        $listener->handle($event);

        $this->assertNull($this->cacheManager->get(CacheKeys::CATEGORIES_TREE));
        $this->assertNull($this->cacheManager->get(CacheKeys::HOT_DEALS));
        $this->assertNull($this->cacheManager->get(CacheKeys::productKey(55)));
    }

    public function test_invalidate_setting_cache_listener(): void
    {
        $this->cacheManager->put(CacheKeys::GENERAL_SETTINGS, ['name' => 'MondolShop'], 300);
        $this->cacheManager->put(CacheKeys::SOCIAL_MEDIA, [['facebook' => 'fb.com']], 300);

        $this->assertNotNull($this->cacheManager->get(CacheKeys::GENERAL_SETTINGS));

        $listener = $this->app->make(InvalidateSettingCacheListener::class);
        $event = new SettingUpdatedEvent('general', ['name' => 'New Name']);
        $listener->handle($event);

        $this->assertNull($this->cacheManager->get(CacheKeys::GENERAL_SETTINGS));
        $this->assertNull($this->cacheManager->get(CacheKeys::SOCIAL_MEDIA));
    }
}