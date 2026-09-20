<?php

use App\Domain\Shared\Contracts\ImageStorage;
use App\Infrastructure\Images\CloudflareImageStorage;
use App\Infrastructure\Images\LocalDiskImageStorage;

test('the local driver resolves the disk storage', function () {
    config(['images.driver' => 'local']);
    app()->forgetInstance(ImageStorage::class);

    expect(resolve(ImageStorage::class))->toBeInstanceOf(LocalDiskImageStorage::class);
});

test('any other driver resolves cloudflare storage', function () {
    config(['images.driver' => 'cloudflare']);
    app()->forgetInstance(ImageStorage::class);

    expect(resolve(ImageStorage::class))->toBeInstanceOf(CloudflareImageStorage::class);
});
