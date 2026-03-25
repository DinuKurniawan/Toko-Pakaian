<?php

use App\Support\ServerlessRuntime;

afterEach(function () {
    deleteDirectory($this->serverlessTempRoot ?? null);
});

test('vercel defaults use writable file-backed drivers', function () {
    expect(ServerlessRuntime::defaultSessionDriver(true))->toBe('file')
        ->and(ServerlessRuntime::defaultCacheStore(true))->toBe('file');
});

test('serverless session cache and view paths are created inside temp storage', function () {
    $this->serverlessTempRoot = uniqueTempRoot();

    $sessionPath = ServerlessRuntime::sessionFilesPath(true, $this->serverlessTempRoot);
    $cachePath = ServerlessRuntime::cachePath(true, $this->serverlessTempRoot);
    $viewPath = ServerlessRuntime::viewCompiledPath(true, $this->serverlessTempRoot);

    expect($sessionPath)->toBe($this->serverlessTempRoot.DIRECTORY_SEPARATOR.'sessions')
        ->and(is_dir($sessionPath))->toBeTrue()
        ->and($cachePath)->toBe($this->serverlessTempRoot.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'data')
        ->and(is_dir($cachePath))->toBeTrue()
        ->and($viewPath)->toBe($this->serverlessTempRoot.DIRECTORY_SEPARATOR.'views')
        ->and(is_dir($viewPath))->toBeTrue();
});

test('serverless sqlite fallback is copied to a writable temp database', function () {
    $this->serverlessTempRoot = uniqueTempRoot();

    $sourceDirectory = $this->serverlessTempRoot.DIRECTORY_SEPARATOR.'source';
    $runtimeDirectory = $this->serverlessTempRoot.DIRECTORY_SEPARATOR.'runtime';
    $sourcePath = $sourceDirectory.DIRECTORY_SEPARATOR.'database.sqlite';
    $targetPath = $runtimeDirectory.DIRECTORY_SEPARATOR.'database.sqlite';

    mkdir($sourceDirectory, 0777, true);
    file_put_contents($sourcePath, 'sqlite-seed');

    $resolvedPath = ServerlessRuntime::sqliteDatabasePath($sourcePath, true, true, $targetPath);

    expect($resolvedPath)->toBe($targetPath)
        ->and(file_exists($targetPath))->toBeTrue()
        ->and(file_get_contents($targetPath))->toBe('sqlite-seed');
});

function uniqueTempRoot(): string
{
    return sys_get_temp_dir().DIRECTORY_SEPARATOR.'toko-pakaian-serverless-'.bin2hex(random_bytes(8));
}

function deleteDirectory(?string $path): void
{
    if ($path === null || ! is_dir($path)) {
        return;
    }

    $entries = scandir($path);

    if ($entries === false) {
        return;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $fullPath = $path.DIRECTORY_SEPARATOR.$entry;

        if (is_dir($fullPath)) {
            deleteDirectory($fullPath);
            continue;
        }

        @unlink($fullPath);
    }

    @rmdir($path);
}
