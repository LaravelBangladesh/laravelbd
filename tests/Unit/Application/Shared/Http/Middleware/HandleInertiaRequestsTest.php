<?php

use App\Application\Shared\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;

test('a locale with no translation file shares an empty translation set', function () {
    app()->setLocale('fr');

    $shared = (new HandleInertiaRequests)->share(Request::create('/'));

    expect($shared['locale'])->toBe('fr')
        ->and($shared['translations'])->toBe([]);
});

test('a locale with a translation file shares its strings', function () {
    app()->setLocale('bn');

    $shared = (new HandleInertiaRequests)->share(Request::create('/'));

    expect($shared['translations'])->not->toBe([])
        ->and($shared['translations'])->toHaveKey('home.hero.title');
});
