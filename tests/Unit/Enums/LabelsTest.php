<?php

use App\Domain\Cfp\Enums\ProposalKind;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;

test('event types expose labels', function (EventType $type, string $key) {
    expect($type->label())->toBe(__($key));
})->with([
    [EventType::Meetup, 'events.types.meetup'],
    [EventType::Workshop, 'events.types.workshop'],
    [EventType::Conference, 'events.types.conference'],
    [EventType::Other, 'events.types.other'],
]);

test('session kinds expose labels', function (SessionKind $kind, string $key) {
    expect($kind->label())->toBe(__($key));
})->with([
    [SessionKind::Talk, 'events.sessions.kinds.talk'],
    [SessionKind::Workshop, 'events.sessions.kinds.workshop'],
    [SessionKind::Panel, 'events.sessions.kinds.panel'],
    [SessionKind::Keynote, 'events.sessions.kinds.keynote'],
    [SessionKind::Break, 'events.sessions.kinds.break'],
    [SessionKind::Other, 'events.sessions.kinds.other'],
]);

test('speaker roles expose labels', function (SpeakerRole $role, string $key) {
    expect($role->label())->toBe(__($key));
})->with([
    [SpeakerRole::Speaker, 'events.speakers.roles.speaker'],
    [SpeakerRole::Host, 'events.speakers.roles.host'],
    [SpeakerRole::Moderator, 'events.speakers.roles.moderator'],
]);

test('registration statuses expose labels', function (RegistrationStatus $status, string $key) {
    expect($status->label())->toBe(__($key));
})->with([
    [RegistrationStatus::Registered, 'events.rsvp.registered'],
    [RegistrationStatus::Cancelled, 'events.rsvp.cancelled'],
    [RegistrationStatus::Waitlisted, 'events.rsvp.waitlisted'],
]);

test('directory kinds expose labels', function (DirectoryKind $kind, string $key) {
    expect($kind->label())->toBe(__($key));
})->with([
    [DirectoryKind::Person, 'directory.kinds.person'],
    [DirectoryKind::Company, 'directory.kinds.company'],
]);

test('resource kinds expose labels', function (ResourceKind $kind, string $key) {
    expect($kind->label())->toBe(__($key));
})->with([
    [ResourceKind::Link, 'resources.kinds.link'],
    [ResourceKind::Video, 'resources.kinds.video'],
    [ResourceKind::Article, 'resources.kinds.article'],
]);

test('proposal kinds expose labels', function (ProposalKind $kind, string $key) {
    expect($kind->label())->toBe(__($key));
})->with([
    [ProposalKind::Talk, 'cfp.kinds.talk'],
    [ProposalKind::Workshop, 'cfp.kinds.workshop'],
    [ProposalKind::Other, 'cfp.kinds.other'],
]);

test('proposal kinds map to session kinds', function (ProposalKind $kind, SessionKind $session) {
    expect($kind->sessionKind())->toBe($session);
})->with([
    [ProposalKind::Talk, SessionKind::Talk],
    [ProposalKind::Workshop, SessionKind::Workshop],
    [ProposalKind::Other, SessionKind::Other],
]);

test('proposal statuses expose labels', function (ProposalStatus $status, string $key) {
    expect($status->label())->toBe(__($key));
})->with([
    [ProposalStatus::Submitted, 'cfp.statuses.submitted'],
    [ProposalStatus::Accepted, 'cfp.statuses.accepted'],
    [ProposalStatus::Rejected, 'cfp.statuses.rejected'],
]);

test('event statuses expose labels', function (EventStatus $status, string $key) {
    expect($status->label())->toBe(__($key));
})->with([
    [EventStatus::Draft, 'events.status.draft'],
    [EventStatus::Published, 'events.status.published'],
    [EventStatus::Cancelled, 'events.status.cancelled'],
]);
