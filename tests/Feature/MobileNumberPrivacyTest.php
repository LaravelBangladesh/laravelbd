<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;

/**
 * The mobile number is private: only its owner and staff ever see it. These
 * checks look for the national digits so no formatting of the number slips
 * through either.
 */
beforeEach(function () {
    $this->member = User::factory()->listedInDirectory()->create([
        'name' => 'Ada Lovelace',
        'slug' => 'ada-lovelace',
        'mobile_number' => '+8801712345678',
    ]);
    $this->digits = '1712345678';

    $this->event = Event::factory()->published()->create(['slug' => 'laracon-bd']);
    EventRegistration::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->member->id,
    ]);
    TalkProposal::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->member->id,
    ]);
});

test('the number never appears on public pages', function (string $url, array $headers) {
    $this->withHeaders($headers)
        ->get($url)
        ->assertOk()
        ->assertDontSee($this->digits, false);
})->with([
    'directory index' => ['/directory', []],
    'directory index markdown' => ['/directory', ['Accept' => 'text/markdown']],
    'directory profile with json-ld' => ['/directory/ada-lovelace', []],
    'directory profile markdown' => ['/directory/ada-lovelace', ['Accept' => 'text/markdown']],
    'event page' => ['/events/laracon-bd', []],
    'event markdown' => ['/events/laracon-bd', ['Accept' => 'text/markdown']],
    'events index' => ['/events', []],
    'sitemap' => ['/sitemap.xml', []],
    'llms.txt' => ['/llms.txt', []],
    'llms-full.txt' => ['/llms-full.txt', []],
]);

test('the number is not shared with the signed in user on every page', function () {
    $this->actingAs($this->member)
        ->get(route('events.show', $this->event))
        ->assertOk()
        ->assertDontSee($this->digits, false);

    $this->actingAs($this->member)
        ->get(route('directory.show', 'ada-lovelace'))
        ->assertOk()
        ->assertDontSee($this->digits, false);
});

test('the owner sees their own number on the profile form', function () {
    $this->actingAs($this->member)
        ->get(route('account.directory.edit'))
        ->assertOk()
        ->assertSee($this->digits, false);
});

/**
 * Speakers are users too, so their email and mobile number must stay off
 * every page and feed that shows a speaker.
 */
test('a speaker\'s email and number never appear where speakers are shown', function (string $url, array $headers) {
    $speaker = User::factory()->withCompleteProfile()->create([
        'name' => 'Grace Hopper',
        'email' => 'grace.private@example.com',
        'mobile_number' => '+8801812345679',
    ]);
    $this->event->speakers()->attach($speaker, ['role' => 'host']);
    EventSession::factory()->create(['event_id' => $this->event->id])
        ->speakers()->attach($speaker, ['role' => 'speaker']);
    Resource::factory()->published()->create([
        'slug' => 'queues-talk',
        'event_id' => $this->event->id,
        'speaker_id' => $speaker->id,
    ]);

    $this->withHeaders($headers)
        ->get($url)
        ->assertOk()
        ->assertSee('Grace Hopper', false)
        ->assertDontSee('1812345679', false)
        ->assertDontSee('grace.private@example.com', false);
})->with([
    'event page' => ['/events/laracon-bd', []],
    'event markdown' => ['/events/laracon-bd', ['Accept' => 'text/markdown']],
    'llms-full.txt' => ['/llms-full.txt', []],
    'resources index' => ['/resources', []],
    'resource page' => ['/resources/queues-talk', []],
]);
