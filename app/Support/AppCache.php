<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class AppCache
{
    private const STOREFRONT_VERSION_KEY = 'storefront:version';
    private const ADMIN_DASHBOARD_KEY = 'dashboard:admin:summary';

    public static function rememberStorefront(string $key, Closure $callback, int $seconds = 600): mixed
    {
        $version = (int) Cache::get(self::STOREFRONT_VERSION_KEY, 1);

        return Cache::remember(
            "storefront:v{$version}:{$key}",
            now()->addSeconds($seconds),
            $callback
        );
    }

    public static function bustStorefront(): void
    {
        if (!Cache::add(self::STOREFRONT_VERSION_KEY, 1, now()->addYear())) {
            Cache::increment(self::STOREFRONT_VERSION_KEY);
        }
    }

    public static function rememberAdminDashboard(Closure $callback, int $seconds = 120): mixed
    {
        return Cache::remember(
            self::ADMIN_DASHBOARD_KEY,
            now()->addSeconds($seconds),
            $callback
        );
    }

    public static function forgetAdminDashboard(): void
    {
        Cache::forget(self::ADMIN_DASHBOARD_KEY);
    }

    public static function forgetCartCount(int $userId): void
    {
        Cache::forget("cart-count:{$userId}");
    }
}
