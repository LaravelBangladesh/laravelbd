<?php

use App\Infrastructure\Images\CloudflareImageStorage;
use App\Infrastructure\Images\LocalDiskImageStorage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function cloudflareConfigured(): void
{
    config([
        'cloudflare.images.account_id' => 'account-1',
        'cloudflare.images.api_token' => 'token-1',
        'cloudflare.images.delivery_url' => 'https://imagedelivery.net/account/',
    ]);
}

function cloudflareStorage(): CloudflareImageStorage
{
    return new CloudflareImageStorage(new LocalDiskImageStorage);
}

test('uploads to cloudflare and returns a prefixed id', function () {
    cloudflareConfigured();
    Http::fake([
        'api.cloudflare.com/*' => Http::response(['result' => ['id' => 'image-id']]),
    ]);

    expect(cloudflareStorage()->put('binary', 'speaker.jpg', 'speakers'))->toBe('cf:image-id');
});

test('throws when cloudflare returns no image id', function () {
    cloudflareConfigured();
    Http::fake([
        'api.cloudflare.com/*' => Http::response(['result' => []]),
    ]);

    expect(fn () => cloudflareStorage()->put('binary', 'speaker.jpg', 'speakers'))
        ->toThrow(RuntimeException::class, 'Cloudflare Images did not return an image id.');
});

test('falls back to the local disk when cloudflare is not configured', function () {
    Storage::fake('public');
    config(['cloudflare.images.account_id' => null]);

    expect(cloudflareStorage()->put('binary', 'speaker.jpg', 'speakers'))->toStartWith('speakers/');
});

test('builds delivery urls for cloudflare paths and delegates the rest', function () {
    Storage::fake('public');
    cloudflareConfigured();

    $storage = cloudflareStorage();

    expect($storage->url(null))->toBeNull()
        ->and($storage->url(''))->toBeNull()
        ->and($storage->url('cf:image-id'))->toBe('https://imagedelivery.net/account/image-id/public')
        ->and($storage->url('speakers/photo.jpg'))->toBe(Storage::disk('public')->url('speakers/photo.jpg'));
});

test('deletes cloudflare images through the api', function () {
    cloudflareConfigured();
    Http::fake(['api.cloudflare.com/*' => Http::response([])]);

    cloudflareStorage()->delete('cf:image-id');

    Http::assertSent(fn ($request) => $request->method() === 'DELETE'
        && str_ends_with($request->url(), '/images/v1/image-id'));
});

test('skips cloudflare deletes when it is not configured', function () {
    config(['cloudflare.images.account_id' => null]);
    Http::fake();

    cloudflareStorage()->delete('cf:image-id');

    Http::assertNothingSent();
});

test('delegates local deletes and ignores empty paths', function () {
    Storage::fake('public');
    cloudflareConfigured();

    Storage::disk('public')->put('speakers/photo.jpg', 'binary');

    $storage = cloudflareStorage();
    $storage->delete(null);
    $storage->delete('');
    $storage->delete('speakers/photo.jpg');

    expect(Storage::disk('public')->exists('speakers/photo.jpg'))->toBeFalse();
});
