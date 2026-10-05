<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\Company;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;

test('the sitemap lists public routes and published entities', function () {
    $event = Event::factory()->published()->create();
    Event::factory()->create();

    $resource = Resource::factory()->published()->create();
    Resource::factory()->create();

    $person = User::factory()->listedInDirectory()->create();
    User::factory()->pendingInDirectory()->create();
    $company = Company::factory()->published()->create();
    Company::factory()->create();

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
        ->toContain(route('terms'))
        ->toContain(route('privacy'))
        ->toContain(route('events.show', $event))
        ->toContain(route('resources.show', $resource))
        ->toContain(route('directory.show', (string) $person->slug))
        ->toContain(route('directory.show', $company->slug));

    expect($response->getContent())->not->toContain(substr((string) $person->mobile_number, 4));
});

test('the sitemap excludes unpublished entities', function () {
    $draftEvent = Event::factory()->create();
    $draftResource = Resource::factory()->create();
    $pendingPerson = User::factory()->pendingInDirectory()->create();
    $draftCompany = Company::factory()->create();

    $response = $this->get(route('sitemap'))->assertOk();

    $xml = simplexml_load_string($response->getContent());
    $locations = [];
    foreach ($xml->url as $url) {
        $locations[] = (string) $url->loc;
    }

    expect($locations)->not->toContain(route('events.show', $draftEvent))
        ->not->toContain(route('resources.show', $draftResource))
        ->not->toContain(route('directory.show', (string) $pendingPerson->slug))
        ->not->toContain(route('directory.show', $draftCompany->slug));
});
