<?php

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MetaDescription;

test('the organization describes the community', function () {
    expect(JsonLd::organization())->toBe([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('app.name'),
        'url' => url('/'),
        'logo' => asset('images/laravel-logo.svg'),
        'sameAs' => ['https://www.facebook.com/groups/laravelbangladesh'],
        'foundingDate' => '2012',
        'areaServed' => 'BD',
    ]);
});

test('the organizer reference only identifies the community', function () {
    expect(JsonLd::organizer())->toBe([
        '@type' => 'Organization',
        'name' => config('app.name'),
        'url' => url('/'),
    ]);
});

test('the website names the site', function () {
    expect(JsonLd::website())->toBe([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name'),
        'url' => url('/'),
    ]);
});

test('a collection page carries its own name, url and description', function () {
    expect(JsonLd::collectionPage('Events', 'https://laravelbd.test/events', 'All meetups.'))
        ->toBe([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Events',
            'url' => 'https://laravelbd.test/events',
            'description' => 'All meetups.',
        ]);
});

test('breadcrumbs are numbered in the order they are given', function () {
    expect(Breadcrumbs::make([
        'Home' => 'https://laravelbd.test/',
        'Events' => 'https://laravelbd.test/events',
        'Laracon' => 'https://laravelbd.test/events/laracon',
    ]))->toBe([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://laravelbd.test/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Events', 'item' => 'https://laravelbd.test/events'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => 'Laracon', 'item' => 'https://laravelbd.test/events/laracon'],
        ],
    ]);
});

test('an empty trail produces an empty breadcrumb list', function () {
    expect(Breadcrumbs::make([])['itemListElement'])->toBe([]);
});

test('a meta description strips markup and collapses whitespace', function () {
    expect(MetaDescription::make("<p>A day   of\n<strong>Laravel</strong> talks.</p>"))
        ->toBe('A day of Laravel talks.');
});

test('a meta description decodes entities', function () {
    expect(MetaDescription::make('Talks &amp; workshops'))->toBe('Talks & workshops');
});

test('a meta description is trimmed to a snippet length', function () {
    $description = MetaDescription::make(str_repeat('word ', 80));

    expect(mb_strlen($description))->toBeLessThanOrEqual(MetaDescription::LIMIT + 3)
        ->and($description)->toEndWith('...');
});

test('a meta description falls through to the first usable candidate', function () {
    expect(MetaDescription::make(null, '  ', '<p></p>', 'The real text.'))
        ->toBe('The real text.');
});

test('a meta description is empty when nothing is usable', function () {
    expect(MetaDescription::make(null, ''))->toBe('');
});
