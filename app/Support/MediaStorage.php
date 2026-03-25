<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorage
{
    public static function diskName(): string
    {
        return (string) config('filesystems.media_disk', 'public');
    }

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, self::diskName());
    }

    public static function delete(array|string|null $paths): void
    {
        $storedPaths = collect(is_array($paths) ? $paths : [$paths])
            ->map(fn ($path) => self::storedPath($path))
            ->filter()
            ->values()
            ->all();

        if ($storedPaths !== []) {
            Storage::disk(self::diskName())->delete($storedPaths);
        }
    }

    public static function url(?string $path): ?string
    {
        $cleanPath = self::cleanPath($path);

        if ($cleanPath === null) {
            return null;
        }

        if (Str::startsWith($cleanPath, ['http://', 'https://'])) {
            return $cleanPath;
        }

        return Storage::disk(self::diskName())->url(self::storedPath($cleanPath));
    }

    public static function storedPath(?string $path): ?string
    {
        $cleanPath = self::cleanPath($path);

        if ($cleanPath === null || Str::startsWith($cleanPath, ['http://', 'https://'])) {
            return null;
        }

        if (str_starts_with($cleanPath, '/storage/')) {
            return Str::after($cleanPath, '/storage/');
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            return Str::after($cleanPath, 'storage/');
        }

        return ltrim($cleanPath, '/');
    }

    private static function cleanPath(?string $path): ?string
    {
        if (!filled($path)) {
            return null;
        }

        $cleanPath = preg_replace('/^["\'\[]+|["\'\]]+$/', '', trim((string) $path));

        if ($cleanPath === null || $cleanPath === '') {
            return null;
        }

        if (str_starts_with($cleanPath, '[http') && str_contains($cleanPath, '](')) {
            $cleanPath = Str::beforeLast(Str::after($cleanPath, ']('), ')');
        }

        return $cleanPath;
    }
}
