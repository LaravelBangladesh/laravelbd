<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;

test('resources respond with markdown when requested', function () {
    $resource = Resource::factory()->published()->create(['title_en' => 'Laravel docs']);

    $this->withHeaders(['Accept' => 'text/markdown'])
        ->get(route('resources.index'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertSee('Laravel docs');

    $this->withHeaders(['Accept' => 'text/markdown'])
        ->get(route('resources.show', $resource))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertSee('# Laravel docs', false);
});

test('directory listings respond with markdown when requested', function () {
    $listing = DirectoryListing::factory()->published()->create(['name' => 'Ada Lovelace']);

    $this->withHeaders(['Accept' => 'text/markdown'])
        ->get(route('directory.index'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertSee('Ada Lovelace');

    $this->withHeaders(['Accept' => 'text/markdown'])
        ->get(route('directory.show', $listing))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertSee('# Ada Lovelace', false);
});

test('events respond with markdown when requested', function () {
    $event = Event::factory()->published()->create(['title_en' => 'Laracon BD']);

    $this->withHeaders(['Accept' => 'text/markdown'])
        ->get(route('events.index'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertSee('Laracon BD');

    $this->withHeaders(['Accept' => 'text/markdown'])
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertSee('# Laracon BD', false);
});

test('browsers without a markdown accept header still get html', function () {
    Resource::factory()->published()->create();

    $this->get(route('resources.index'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
});

test('appending .md to a url serves its markdown version', function () {
    $event = Event::factory()->published()->create(['title_en' => 'Laracon BD']);

    $this->get(route('events.show', $event).'.md')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->assertHeader('Link', '<'.route('events.show', $event).'>; rel="canonical"')
        ->assertSee('# Laracon BD', false);
});

test('the .md suffix keeps the query string', function () {
    Event::factory()->published()->create(['title_en' => 'A meetup']);
    Event::factory()->published()->create(['title_en' => 'A workshop', 'type' => 'workshop']);

    $this->get(route('events.index').'.md?type=workshop')
        ->assertOk()
        ->assertSee('A workshop')
        ->assertDontSee('A meetup');
});

test('the .md suffix is ignored for requests that change state', function () {
    $this->post('/locale.md', ['locale' => 'bn'])->assertSessionMissing('locale');
});

test('responses tell caches they vary on the accept header', function () {
    expect($this->get(route('home'))->baseResponse->getVary())->toContain('Accept', 'X-Inertia');
    expect($this->get(route('events.index').'.md')->baseResponse->getVary())->toContain('Accept');
});

test('static pages respond with markdown', function (string $url, array $expected) {
    $response = $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8');

    foreach ($expected as $text) {
        $response->assertSee($text, false);
    }
})->with([
    'home' => ['/index.md', ['# Laravel developers of Bangladesh, together since 2012', '## Upcoming events', 'No upcoming events are scheduled right now.', '## Run by the community, for the community']],
    'about' => ['/about.md', ['# The Laravel community of Bangladesh since 2012', '## What we do', '### Meetups and workshops', '- Students and career changers', '## Across Bangladesh', 'Dhaka · Chattogram']],
    'terms' => ['/terms.md', ['# Terms of Use', 'Last updated 27 September 2026', '## About these terms', '## Contact']],
    'privacy' => ['/privacy.md', ['# Privacy Policy', '## What we collect', '- Account: your name']],
]);

test('the home page lists upcoming events in markdown', function () {
    $event = Event::factory()->published()->create(['title_en' => 'Laracon BD']);

    $this->get('/index.md')
        ->assertOk()
        ->assertSee('- [Laracon BD]('.route('events.show', $event).')', false)
        ->assertDontSee('No upcoming events are scheduled right now.');
});
