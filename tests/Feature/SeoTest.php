<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('every page shares the canonical url, default image and site name', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('seo.url', route('home'))
            ->where('seo.default_image', asset('images/og-default.webp'))
            ->where('seo.site_name', config('app.name'))
        );
});

test('the canonical url drops the query string', function () {
    $this->get(route('events.index').'?type=meetup')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('seo.url', route('events.index')));
});

test('the home page carries the organization and website structured data', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('json_ld.0.@type', 'Organization')
            ->where('json_ld.0.foundingDate', '2012')
            ->where('json_ld.0.sameAs.0', 'https://www.facebook.com/groups/laravelbangladesh')
            ->where('json_ld.1.@type', 'WebSite')
        );
});

test('the home page counts published events for the facts row', function () {
    Event::factory()->published()->count(2)->create();
    Event::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('stats.events', 2));
});

test('the about page carries its breadcrumbs', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('json_ld.0.@type', 'BreadcrumbList')
            ->where('json_ld.0.itemListElement.1.name', 'About')
        );
});

test('an index page carries a collection page and breadcrumbs', function (string $route, string $name) {
    $this->get(route($route))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('json_ld.0.@type', 'CollectionPage')
            ->where('json_ld.0.name', $name)
            ->where('json_ld.0.url', route($route))
            ->where('json_ld.1.@type', 'BreadcrumbList')
        );
})->with([
    ['events.index', 'Events'],
    ['directory.index', 'Directory'],
    ['resources.index', 'Resources'],
]);

test('an event page carries its description and structured data', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'laracon-dhaka',
        'title_en' => 'Laracon Dhaka',
        'excerpt_en' => '<p>A day of Laravel talks.</p>',
    ]);

    $this->get(route('events.show', $event->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.meta_description', 'A day of Laravel talks.')
            ->where('event.json_ld.0.@type', 'Event')
            ->where('event.json_ld.0.name', 'Laracon Dhaka')
            ->where('event.json_ld.1.@type', 'BreadcrumbList')
            ->where('event.json_ld.1.itemListElement.2.name', 'Laracon Dhaka')
        );
});

test('a directory page carries its description and structured data', function () {
    $listing = DirectoryListing::factory()->published()->create([
        'slug' => 'ada-lovelace',
        'name' => 'Ada Lovelace',
        'bio_en' => 'Builds Laravel applications in Dhaka.',
    ]);

    $this->get(route('directory.show', $listing->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('listing.meta_description', 'Builds Laravel applications in Dhaka.')
            ->where('listing.json_ld.0.@type', 'Person')
            ->where('listing.json_ld.0.name', 'Ada Lovelace')
            ->where('listing.json_ld.1.@type', 'BreadcrumbList')
        );
});

test('a listing without a bio still describes itself', function () {
    $listing = DirectoryListing::factory()->published()->create([
        'name' => 'Ada Lovelace',
        'title' => 'Principal Engineer',
        'company' => 'Analytical Engines',
        'city' => 'Dhaka',
        'bio_en' => null,
        'bio_bn' => null,
    ]);

    $this->get(route('directory.show', $listing->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where(
            'listing.meta_description',
            'Ada Lovelace · Principal Engineer · Analytical Engines · Dhaka',
        ));
});

test('a resource page carries its description and structured data', function () {
    $resource = Resource::factory()->published()->create([
        'slug' => 'queues-in-depth',
        'title_en' => 'Queues in depth',
        'excerpt_en' => 'How Laravel queues work.',
    ]);

    $this->get(route('resources.show', $resource->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.meta_description', 'How Laravel queues work.')
            ->where('resource.json_ld.0.@type', 'Article')
            ->where('resource.json_ld.1.@type', 'BreadcrumbList')
        );
});
