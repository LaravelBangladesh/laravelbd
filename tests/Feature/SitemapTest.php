<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;

test('the sitemap lists public routes and published entities', function () {
    $event = Event::factory()->published()->create();
    Event::factory()->create();

    $resource = Resource::factory()->published()->create();
    Resource::factory()->create();

    $listing = DirectoryListing::factory()->published()->create();
    DirectoryListing::factory()->create();

    $response = $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse();
    expect($xml->getName())->toBe('urlset');

    $locations = [];
    foreach ($xml->url as $url) {
        $locations[] = (string) $url->loc;
    }

    expect($locations)->toContain(route('home'))
        ->toContain(route('about'))
        ->toContain(route('events.index'))
        ->toContain(route('resources.index'))
        ->toContain(route('directory.index'))
        ->toContain(route('events.show', $event))
        ->toContain(route('resources.show', $resource))
        ->toContain(route('directory.show', $listing));
});

test('the sitemap excludes unpublished entities', function () {
    $draftEvent = Event::factory()->create();
    $draftResource = Resource::factory()->create();
    $draftListing = DirectoryListing::factory()->create();

    $response = $this->get(route('sitemap'))->assertOk();

    $xml = simplexml_load_string($response->getContent());
    $locations = [];
    foreach ($xml->url as $url) {
        $locations[] = (string) $url->loc;
    }

    expect($locations)->not->toContain(route('events.show', $draftEvent))
        ->not->toContain(route('resources.show', $draftResource))
        ->not->toContain(route('directory.show', $draftListing));
});
