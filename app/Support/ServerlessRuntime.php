<?php

namespace App\Support;

use RuntimeException;

class ServerlessRuntime
{
    public static function isVercel(?bool $isVercel = null): bool
    {
        if ($isVercel !== null) {
            return $isVercel;
        }

        return filter_var(env('VERCEL', false), FILTER_VALIDATE_BOOL);
    }

    public static function defaultSessionDriver(?bool $isVercel = null): string
    {
        return self::isVercel($isVercel) ? 'file' : 'database';
    }

    public static function defaultCacheStore(?bool $isVercel = null): string
    {
        return self::isVercel($isVercel) ? 'file' : 'database';
    }

    public static function sessionFilesPath(?bool $isVercel = null, ?string $tempRoot = null): string
    {
        $path = self::isVercel($isVercel)
            ? self::joinPaths(self::tempRoot($tempRoot), 'sessions')
            : storage_path('framework/sessions');

        return self::ensureDirectory($path);
    }

    public static function cachePath(?bool $isVercel = null, ?string $tempRoot = null): string
    {
        $path = self::isVercel($isVercel)
            ? self::joinPaths(self::tempRoot($tempRoot), 'cache', 'data')
            : storage_path('framework/cache/data');

        return self::ensureDirectory($path);
    }

    public static function viewCompiledPath(?bool $isVercel = null, ?string $tempRoot = null): string
    {
        $path = self::isVercel($isVercel)
            ? self::joinPaths(self::tempRoot($tempRoot), 'views')
            : storage_path('framework/views');

        return self::ensureDirectory($path);
    }

    public static function sqliteDatabasePath(
        string $configuredPath,
        ?bool $isVercel = null,
        ?bool $copyToTmp = null,
        ?string $tempPath = null,
    ): string {
        if ($configuredPath === ':memory:') {
            return $configuredPath;
        }

        $shouldCopyToTmp = $copyToTmp ?? self::isVercel($isVercel);

        if (! self::isVercel($isVercel) || ! $shouldCopyToTmp) {
            return $configuredPath;
        }

        $sourcePath = $configuredPath;
        $targetPath = $tempPath ?: self::joinPaths(self::tempRoot(), 'database.sqlite');

        if (self::normalizePath($sourcePath) === self::normalizePath($targetPath)) {
            self::ensureDirectory(dirname($targetPath));

            return $targetPath;
        }

        if (! is_file($sourcePath)) {
            throw new RuntimeException("Configured SQLite database not found at [{$configuredPath}].");
        }

        self::ensureDirectory(dirname($targetPath));

        $needsCopy = ! is_file($targetPath)
            || filesize($sourcePath) !== filesize($targetPath)
            || filemtime($sourcePath) > filemtime($targetPath);

        if ($needsCopy && ! copy($sourcePath, $targetPath)) {
            throw new RuntimeException("Unable to copy SQLite database to writable path [{$targetPath}].");
        }

        return $targetPath;
    }

    public static function tempRoot(?string $tempRoot = null): string
    {
        return rtrim(
            (string) ($tempRoot ?: env('TMPDIR') ?: sys_get_temp_dir()),
            "\\/"
        );
    }

    private static function ensureDirectory(string $path): string
    {
        if (is_dir($path)) {
            return $path;
        }

        if (! mkdir($path, 0777, true) && ! is_dir($path)) {
            throw new RuntimeException("Unable to create writable directory [{$path}].");
        }

        return $path;
    }

    private static function joinPaths(string ...$segments): string
    {
        $cleanSegments = array_map(
            static fn (string $segment) => trim($segment, "\\/"),
            array_filter($segments, static fn (?string $segment) => $segment !== null && $segment !== '')
        );

        if ($cleanSegments === []) {
            return '';
        }

        $prefix = str_starts_with($segments[0], DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

        return $prefix.implode(DIRECTORY_SEPARATOR, $cleanSegments);
    }

    private static function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
