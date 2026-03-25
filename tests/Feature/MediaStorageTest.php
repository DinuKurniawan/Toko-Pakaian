<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config([
        'filesystems.media_disk' => 'media-test',
        'filesystems.disks.media-test' => [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/media-test'),
            'url' => 'https://cdn.example.com/media',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],
    ]);
});

test('product media attributes resolve urls from the configured media disk', function () {
    $product = new Product();
    $product->setRawAttributes([
        'image' => 'products/main.jpg',
        'images' => json_encode(['products/gallery-1.jpg', 'products/gallery-2.jpg'], JSON_THROW_ON_ERROR),
    ], true);

    expect($product->image)->toBe('https://cdn.example.com/media/products/main.jpg');
    expect($product->images)->toBe([
        'https://cdn.example.com/media/products/gallery-1.jpg',
        'https://cdn.example.com/media/products/gallery-2.jpg',
    ]);
});

test('product media deletion uses the configured media disk and raw stored paths', function () {
    Storage::disk('media-test')->put('products/main.jpg', 'main');
    Storage::disk('media-test')->put('products/gallery-1.jpg', 'gallery');

    $product = new Product();
    $product->setRawAttributes([
        'image' => 'products/main.jpg',
        'images' => json_encode(['products/gallery-1.jpg'], JSON_THROW_ON_ERROR),
    ], true);

    $product->deleteStoredMedia();

    Storage::disk('media-test')->assertMissing('products/main.jpg');
    Storage::disk('media-test')->assertMissing('products/gallery-1.jpg');
});
