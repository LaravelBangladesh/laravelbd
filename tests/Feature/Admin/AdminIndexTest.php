<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the proposal list shows each submitter', function () {
    $moderator = User::factory()->moderator()->create();
    $submitter = User::factory()->create(['name' => 'Grace Hopper']);
    TalkProposal::factory()->create([
        'title_en' => 'Compilers',
        'user_id' => $submitter->id,
    ]);

    $this->actingAs($moderator)
        ->get(route('admin.proposals.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/proposals/index')
            ->has('proposals', 1)
            ->where('proposals.0.title', 'Compilers')
            ->where('proposals.0.submitter', 'Grace Hopper'));
});

test('the resource list shows each status label', function () {
    $moderator = User::factory()->moderator()->create();
    Resource::factory()->published()->create(['title_en' => 'Docs deep dive']);

    $this->actingAs($moderator)
        ->get(route('admin.resources.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/resources/index')
            ->has('resources', 1)
            ->where('resources.0.title', 'Docs deep dive')
            ->where('resources.0.status', 'published'));
});

test('the directory list shows each status label', function () {
    $moderator = User::factory()->moderator()->create();
    DirectoryListing::factory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($moderator)
        ->get(route('admin.directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/directory/index')
            ->has('listings', 1)
            ->where('listings.0.name', 'Ada Lovelace')
            ->where('listings.0.status', 'draft'));
});

test('the event list shows every event newest first', function () {
    $moderator = User::factory()->moderator()->create();
    Event::factory()->create(['title_en' => 'Older', 'starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour()]);
    Event::factory()->create(['title_en' => 'Newer', 'starts_at' => now()->addDays(9), 'ends_at' => now()->addDays(9)->addHour()]);

    $this->actingAs($moderator)
        ->get(route('admin.events.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/index')
            ->has('events', 2)
            ->where('events.0.title', 'Newer')
            ->where('events.1.title', 'Older'));
});

test('staff can open the resource create form with its options', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create(['title_en' => 'April meetup']);
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($moderator)
        ->get(route('admin.resources.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/resources/create')
            ->has('kinds')
            ->has('statuses')
            ->where('events.0.value', $event->id)
            ->where('events.0.label', 'April meetup')
            ->where('speakers.0.value', $speaker->id)
            ->where('speakers.0.label', 'Ada Lovelace'));
});

test('staff can open the directory create form with its options', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.directory.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/directory/create')
            ->has('kinds')
            ->has('statuses'));
});

test('staff can open the event create form with its options', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/create')
            ->has('types')
            ->has('statuses'));
});
