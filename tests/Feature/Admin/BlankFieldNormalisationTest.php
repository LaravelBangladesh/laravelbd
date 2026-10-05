<?php

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\Company;
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

test('blank company fields are stored as null', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.directory.store'), [
            'name' => 'Analytical Engines',
            'status' => 'draft',
            'title' => '',
            'city' => '',
            'website' => '',
            'github' => '',
            'linkedin' => '',
            'x' => '',
        ])
        ->assertRedirect(route('admin.directory.index'));

    $company = Company::query()->where('name', 'Analytical Engines')->first();

    expect($company?->title)->toBeNull()
        ->and($company?->city)->toBeNull()
        ->and($company?->website)->toBeNull();
});

test('blank profile fields are stored as null for the acting member', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Grace Hopper',
            'title' => '',
            'company' => '',
            'city' => '',
            'website' => '',
            'github' => '',
            'linkedin' => '',
            'x' => '',
            'mobile_number' => '',
        ])
        ->assertRedirect();

    $member->refresh();

    expect($member->name)->toBe('Grace Hopper')
        ->and($member->title)->toBeNull()
        ->and($member->github)->toBeNull()
        ->and($member->mobile_number)->toBeNull();
});
