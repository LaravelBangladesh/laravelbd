<?php

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;

test('a blank review note is stored as null', function () {
    $moderator = User::factory()->moderator()->create();
    $proposal = TalkProposal::factory()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.proposals.update', $proposal), [
            'status' => ProposalStatus::Rejected->value,
            'notes' => '',
            'event_id' => $proposal->event_id,
        ])
        ->assertRedirect(route('admin.proposals.index'));

    $proposal->refresh();

    expect($proposal->notes)->toBeNull()
        ->and($proposal->status)->toBe(ProposalStatus::Rejected);
});

test('blank resource links and relations are stored as null', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.resources.store'), [
            'title_en' => 'A written guide',
            'kind' => 'article',
            'status' => 'draft',
            'url' => 'https://laravel.com/docs',
            'embed_url' => '',
            'event_id' => '',
            'speaker_id' => '',
        ])
        ->assertRedirect(route('admin.resources.index'));

    $resource = Resource::query()->where('title_en', 'A written guide')->first();

    expect($resource?->embed_url)->toBeNull()
        ->and($resource?->event_id)->toBeNull()
        ->and($resource?->speaker_id)->toBeNull();
});

test('a blank event capacity is stored as null', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'Open house',
            'type' => 'meetup',
            'status' => 'draft',
            'starts_at' => now()->addDays(5)->toDateTimeString(),
            'ends_at' => now()->addDays(5)->addHours(3)->toDateTimeString(),
            'capacity' => '',
        ])
        ->assertRedirect();

    expect(Event::query()->where('title_en', 'Open house')->value('capacity'))->toBeNull();
});

test('blank directory listing fields are stored as null', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.directory.store'), [
            'name' => 'Ada Lovelace',
            'kind' => 'person',
            'status' => 'draft',
            'title' => '',
            'company' => '',
            'city' => '',
            'website' => '',
            'github' => '',
            'linkedin' => '',
            'x' => '',
        ])
        ->assertRedirect(route('admin.directory.index'));

    $listing = DirectoryListing::query()->where('name', 'Ada Lovelace')->first();

    expect($listing?->title)->toBeNull()
        ->and($listing?->company)->toBeNull()
        ->and($listing?->city)->toBeNull()
        ->and($listing?->website)->toBeNull();
});

test('blank directory profile fields are stored as null for the acting member', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('account.directory.store'), [
            'name' => 'Grace Hopper',
            'title' => '',
            'company' => '',
            'city' => '',
            'website' => '',
            'github' => '',
            'linkedin' => '',
            'x' => '',
        ])
        ->assertRedirect();

    $listing = DirectoryListing::query()->where('user_id', $member->id)->first();

    expect($listing?->name)->toBe('Grace Hopper')
        ->and($listing?->title)->toBeNull()
        ->and($listing?->github)->toBeNull();
});
