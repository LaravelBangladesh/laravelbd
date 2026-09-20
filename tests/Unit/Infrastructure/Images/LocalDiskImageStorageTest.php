<?php

use App\Infrastructure\Images\LocalDiskImageStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

test('stores image bytes on the public disk', function () {
    Storage::fake('public');

    $path = (new LocalDiskImageStorage)->put('binary', 'speaker.JPG', 'speakers');

    expect($path)->toStartWith('speakers/')
        ->and($path)->toEndWith('.jpg')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

test('falls back to a jpg extension when the filename has none', function () {
    Storage::fake('public');

    expect((new LocalDiskImageStorage)->put('binary', 'speaker', 'speakers'))->toEndWith('.jpg');
});

test('builds disk urls and ignores empty paths', function () {
    Storage::fake('public');

    $storage = new LocalDiskImageStorage;

    expect($storage->url(null))->toBeNull()
        ->and($storage->url(''))->toBeNull()
        ->and($storage->url('speakers/photo.jpg'))->toBe(Storage::disk('public')->url('speakers/photo.jpg'));
});

test('deletes disk files and ignores empty paths', function () {
    Storage::fake('public');

    Storage::disk('public')->put('speakers/photo.jpg', 'binary');

    $storage = new LocalDiskImageStorage;
    $storage->delete(null);
    $storage->delete('');
    $storage->delete('speakers/photo.jpg');

    expect(Storage::disk('public')->exists('speakers/photo.jpg'))->toBeFalse();
});

test('throws when the disk refuses the write', function () {
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('put')->once()->andReturnFalse();

    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    expect(fn () => (new LocalDiskImageStorage)->put('binary', 'a.jpg', 'speakers'))
        ->toThrow(RuntimeException::class, 'Unable to store the uploaded image.');
});
