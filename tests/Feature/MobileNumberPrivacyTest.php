<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
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
