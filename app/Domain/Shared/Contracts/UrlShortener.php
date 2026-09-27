<?php

namespace App\Domain\Shared\Contracts;

interface UrlShortener
{
    /**
     * Return a short link to the given url. Calls with the same idempotency
     * key return the same link instead of creating another one.
     */
    public function shorten(string $url, string $idempotencyKey): string;
}
