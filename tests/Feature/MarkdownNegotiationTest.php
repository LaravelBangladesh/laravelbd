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
