<?php

use App\Domain\Shared\Contracts\ImageStorage;
use App\Infrastructure\Images\CloudflareImageStorage;
use App\Infrastructure\Images\DiskImageStorage;

test('the local driver resolves the disk storage', function () {
    config(['images.driver' => 'local']);
    app()->forgetInstance(ImageStorage::class);

    expect(resolve(ImageStorage::class))->toBeInstanceOf(DiskImageStorage::class);
});

test('the r2 driver resolves disk storage on the r2 disk', function () {
    config(['images.driver' => 'r2']);
    app()->forgetInstance(ImageStorage::class);

    expect(resolve(ImageStorage::class))->toBeInstanceOf(DiskImageStorage::class);
});

test('any other driver resolves cloudflare storage', function () {
    config(['images.driver' => 'cloudflare']);
    app()->forgetInstance(ImageStorage::class);

    expect(resolve(ImageStorage::class))->toBeInstanceOf(CloudflareImageStorage::class);
});
