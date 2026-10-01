<?php

namespace Shared\Infrastructure\Cache;

final class CacheKeys
{
    public const CATEGORIES_TREE = 'catalog:categories:tree';
    public const HOT_DEALS = 'catalog:products:hot_deals';
    public const TOP_RATED = 'catalog:products:top_rated';
    public const GENERAL_SETTINGS = 'settings:general';
    public const SOCIAL_MEDIA = 'settings:social_media';
    public const CONTACT_INFO = 'settings:contact_info';

    public const TTL_SHORT = 300;     // 5 minutes
    public const TTL_MEDIUM = 3600;   // 1 hour
    public const TTL_LONG = 86400;    // 24 hours

    public static function productKey(int $id): string
    {
        return "catalog:product:{$id}";
    }

    public static function pageKey(string $slug): string
    {
        return "settings:page:{$slug}";
    }
}