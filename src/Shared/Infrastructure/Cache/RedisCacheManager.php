<?php

namespace Shared\Infrastructure\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RedisCacheManager
{
    public function remember(string $key, int $ttlSeconds, Closure $callback): mixed
    {
        try {
            return Cache::remember($key, $ttlSeconds, $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::get($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    public function put(string $key, mixed $value, int $ttlSeconds): bool
    {
        try {
            return Cache::put($key, $value, $ttlSeconds);
        } catch (Throwable) {
            return false;
        }
    }

    public function forget(string $key): bool
    {
        try {
            return Cache::forget($key);
        } catch (Throwable) {
            return false;
        }
    }

    public function forgetMultiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }
}