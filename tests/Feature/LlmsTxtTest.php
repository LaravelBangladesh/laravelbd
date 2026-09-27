<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;

test('llms.txt returns a plain-text summary with key section links', function () {
    $response = $this->get(route('llms.txt'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $content = $response->getContent();

    expect($content)->toContain(config('app.name'))
        ->toContain(route('about'))
        ->toContain(route('events.index'))
        ->toContain(route('directory.index'))
        ->toContain(route('resources.index'))
        ->toContain(route('terms'))
        ->toContain(route('privacy'))
        ->toContain('append `.md` to its URL')
        ->toContain(route('llms-full.txt'));
});

test('llms.txt links upcoming and recent events and the latest resources', function () {
    $upcoming = Event::factory()->published()->create(['title_en' => 'Next meetup']);
    $past = Event::factory()->published()->past()->create(['title_en' => 'Last meetup']);
    Event::factory()->create(['title_en' => 'Draft meetup']);
    $resource = Resource::factory()->published()->create(['title_en' => 'Queues in depth']);

    $content = $this->get(route('llms.txt'))->getContent();

    expect($content)
        ->toContain("## Upcoming events\n\n- [Next meetup](".route('events.show', $upcoming).'.md)')
        ->toContain("## Recent events\n\n- [Last meetup](".route('events.show', $past).'.md)')
        ->toContain('- [Queues in depth]('.route('resources.show', $resource).'.md)')
        ->not->toContain('Draft meetup');
});

test('llms-full.txt carries the full details of every published event', function () {
    $event = Event::factory()->published()->create(['title_en' => 'Laracon BD']);
    EventSession::factory()->create(['event_id' => $event->id, 'title_en' => 'Queues in depth']);
    Event::factory()->published()->past()->create(['title_en' => 'Last meetup']);
    Event::factory()->create(['title_en' => 'Draft meetup']);

    $content = $this->get(route('llms-full.txt'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->getContent();

    expect($content)
        ->toContain("## Laracon BD\n\n- **Status:** Upcoming")
        ->toContain('Talk: Queues in depth')
        ->toContain("---\n\n## Last meetup")
        ->not->toContain('Draft meetup');
});
