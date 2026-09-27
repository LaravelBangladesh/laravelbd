<?php

namespace App\Infrastructure\ShortUrls;

use App\Domain\Shared\Contracts\UrlShortener;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Public mol.la API, documented at https://mol.la/app/developers.
 */
class MollaUrlShortener implements UrlShortener
{
    private const ENDPOINT = 'https://mol.la/api/v1/links';

    public function shorten(string $url, string $idempotencyKey): string
    {
        $shortUrl = Http::acceptJson()
            ->timeout(10)
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post(self::ENDPOINT, ['long_url' => $url])
            ->throw()
            ->json('short_url');

        if (! is_string($shortUrl) || $shortUrl === '') {
            throw new RuntimeException('mol.la did not return a short url.');
        }

        return $shortUrl;
    }
}
