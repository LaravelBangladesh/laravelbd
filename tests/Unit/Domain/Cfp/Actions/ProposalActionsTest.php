<?php

use App\Domain\Cfp\Actions\ReviewProposal;
use App\Domain\Cfp\Actions\ScheduleAcceptedProposal;
use App\Domain\Cfp\Actions\SubmitProposal;
use App\Domain\Cfp\Data\ProposalData;
use App\Domain\Cfp\Data\ProposalReviewData;
use App\Domain\Cfp\Enums\ProposalKind;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('submits a proposal for the acting user', function () {
    $user = User::factory()->withCompleteProfile()->create();
    $event = Event::factory()->acceptingProposals()->create();

    $proposal = app(SubmitProposal::class)(ProposalData::fromValidated([
        'title_en' => 'Pest in practice',
        'abstract_en' => 'How we test.',
        'kind' => ProposalKind::Talk->value,
    ]), $event, $user);

    expect($proposal->exists)->toBeTrue()
        ->and($proposal->status)->toBe(ProposalStatus::Submitted)
        ->and($proposal->user_id)->toBe($user->id)
        ->and($proposal->event_id)->toBe($event->id);
});

test('refuses to submit against an event outside its cfp window', function () {
    $user = User::factory()->create();
    $event = Event::factory()->cfpClosed()->create();

    expect(fn () => app(SubmitProposal::class)(ProposalData::fromValidated([
        'title_en' => 'Too late',
        'abstract_en' => 'The window closed.',
        'kind' => ProposalKind::Talk->value,
    ]), $event, $user))->toThrow(ValidationException::class, __('cfp.closed'));

    expect(TalkProposal::query()->count())->toBe(0);
});

test('accepting a proposal creates a session on the event', function () {
    $event = Event::factory()->create();
    $proposal = TalkProposal::factory()->create([
        'kind' => ProposalKind::Workshop,
        'title_en' => 'Pest in practice',
        'abstract_en' => 'How we test.',
    ]);

    $session = app(ScheduleAcceptedProposal::class)($proposal, $event);
    $session->load('speakers');

    expect($proposal->fresh())
        ->status->toBe(ProposalStatus::Accepted)
        ->event_id->toBe($event->id)
        ->event_session_id->toBe($session->id)
        ->and($session->event_id)->toBe($event->id)
        ->and($session->title_en)->toBe('Pest in practice')
        ->and($session->description_en)->toBe('How we test.')
        ->and($session->kind)->toBe(SessionKind::Workshop)
        ->and($session->speakers)->toHaveCount(1)
        ->and($session->speakers->first()?->name)->toBe($proposal->submitter?->name);
});

test('accepting again does not create a second session', function () {
    $event = Event::factory()->create();
    $proposal = TalkProposal::factory()->create();
    $schedule = app(ScheduleAcceptedProposal::class);

    $first = $schedule($proposal, $event);
    $second = $schedule($proposal->fresh(), $event);

    expect($second->id)->toBe($first->id)
        ->and(EventSession::query()->where('event_id', $event->id)->count())->toBe(1);
});

test('reviewing a proposal as accepted schedules it', function () {
    $event = Event::factory()->create();
    $proposal = TalkProposal::factory()->create();

    app(ReviewProposal::class)($proposal, ProposalReviewData::fromValidated([
        'status' => ProposalStatus::Accepted->value,
        'notes' => 'Great fit.',
        'event_id' => $event->id,
    ]));

    expect($proposal->fresh())
        ->status->toBe(ProposalStatus::Accepted)
        ->notes->toBe('Great fit.')
        ->event_session_id->not->toBeNull();
});

test('reviewing a proposal as rejected only saves the review', function () {
    $proposal = TalkProposal::factory()->create();

    app(ReviewProposal::class)($proposal, ProposalReviewData::fromValidated([
        'status' => ProposalStatus::Rejected->value,
        'event_id' => $proposal->event_id,
    ]));

    expect($proposal->fresh())
        ->status->toBe(ProposalStatus::Rejected)
        ->event_session_id->toBeNull();
});

test('clamps the session slot to the end of a short event', function () {
    $event = Event::factory()->create([
        'starts_at' => now()->addDays(5)->setTime(10, 0),
        'ends_at' => now()->addDays(5)->setTime(10, 30),
    ]);
    $proposal = TalkProposal::factory()->create();

    $session = app(ScheduleAcceptedProposal::class)($proposal, $event);

    expect($session->starts_at->equalTo($event->starts_at))->toBeTrue()
        ->and($session->ends_at->equalTo($event->ends_at))->toBeTrue();
});

test('starts the slot after the last session already on the event', function () {
    $event = Event::factory()->create([
        'starts_at' => now()->addDays(5)->setTime(10, 0),
        'ends_at' => now()->addDays(5)->setTime(18, 0),
    ]);
    $existing = EventSession::factory()->create([
        'event_id' => $event->id,
        'starts_at' => $event->starts_at,
        'ends_at' => $event->starts_at->copy()->addHour(),
    ]);
    $proposal = TalkProposal::factory()->create();

    $session = app(ScheduleAcceptedProposal::class)($proposal, $event);

    expect($session->starts_at->equalTo($existing->ends_at))->toBeTrue();
});

test('reuses an existing speaker with the submitter name', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create(['name' => 'Grace Hopper']);
    Speaker::factory()->create(['name' => 'Grace Hopper']);
    $proposal = TalkProposal::factory()->create(['user_id' => $user->id]);

    $session = app(ScheduleAcceptedProposal::class)($proposal, $event);

    expect(Speaker::query()->where('name', 'Grace Hopper')->count())->toBe(1)
        ->and($session->speakers()->first()?->name)->toBe('Grace Hopper');
});

test('a submitter who never set a name is billed as a generic speaker', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create(['name' => '']);
    $proposal = TalkProposal::factory()->create(['user_id' => $user->id]);

    $session = app(ScheduleAcceptedProposal::class)($proposal, $event);

    expect($session->speakers()->first()?->name)->toBe('Speaker')
        ->and(Speaker::query()->where('name', 'Speaker')->exists())->toBeTrue();
});

test('refuses to submit while the profile is incomplete', function () {
    $user = User::factory()->create();
    $event = Event::factory()->acceptingProposals()->create();

    expect(fn () => app(SubmitProposal::class)(ProposalData::fromValidated([
        'title_en' => 'Not yet',
        'abstract_en' => 'The profile is thin.',
        'kind' => ProposalKind::Talk->value,
    ]), $event, $user))->toThrow(ValidationException::class, __('profile.incomplete'));

    expect(TalkProposal::query()->count())->toBe(0);
});
