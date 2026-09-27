<?php

namespace Tests\Fakes;

use App\Domain\Shared\Contracts\UrlShortener;

class FakeUrlShortener implements UrlShortener
{
    /** @var list<array{url: string, key: string}> */
    public array $calls = [];

    public function shorten(string $url, string $idempotencyKey): string
    {
        $this->calls[] = ['url' => $url, 'key' => $idempotencyKey];

        return 'https://mol.la/'.substr(md5($idempotencyKey), 0, 7);
    }
}
