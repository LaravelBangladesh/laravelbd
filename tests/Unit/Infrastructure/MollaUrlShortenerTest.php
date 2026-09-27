<?php

use App\Infrastructure\ShortUrls\MollaUrlShortener;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

test('it creates a short link with an idempotency key', function () {
    Http::fake(['mol.la/api/v1/links' => Http::response([
        'short_code' => 'abc1234',
        'short_url' => 'https://mol.la/abc1234',
    ], 201)]);

    $shortUrl = (new MollaUrlShortener)->shorten('https://www.laravelbd.com/e/1', 'key-1');

    expect($shortUrl)->toBe('https://mol.la/abc1234');

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->url() === 'https://mol.la/api/v1/links'
        && $request->header('Idempotency-Key') === ['key-1']
        && $request->data() === ['long_url' => 'https://www.laravelbd.com/e/1']);
});

test('it fails when mol.la answers without a short url', function () {
    Http::fake(['mol.la/api/v1/links' => Http::response(['error' => 'nope'], 201)]);

    (new MollaUrlShortener)->shorten('https://www.laravelbd.com/e/1', 'key-1');
})->throws(RuntimeException::class, 'mol.la did not return a short url.');

test('it fails when mol.la rejects the request', function () {
    Http::fake(['mol.la/api/v1/links' => Http::response(['error' => 'RATE_LIMITED'], 429)]);

    (new MollaUrlShortener)->shorten('https://www.laravelbd.com/e/1', 'key-1');
})->throws(RequestException::class);
