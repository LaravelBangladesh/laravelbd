<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\Company;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('application models persist uuid v7 primary keys', function (string $model) {
    $record = $model::factory()->create();

    expect($record->id)->toBeString()
        ->and(Str::isUuid($record->id))->toBeTrue()
        ->and($record->getKeyType())->toBe('string')
        ->and($record->getIncrementing())->toBeFalse();
})->with([
    User::class,
    Event::class,
    EventSession::class,
    EventRegistration::class,
    Resource::class,
    Company::class,
    TalkProposal::class,
]);

test('login challenges get a uuid primary key', function () {
    $challenge = LoginChallenge::query()->create([
        'email' => 'guest@example.com',
        'code_hash' => 'hash',
        'token_hash' => 'token',
        'expires_at' => now()->addMinutes(15),
    ]);

    expect(Str::isUuid($challenge->id))->toBeTrue()
        ->and($challenge->isActive())->toBeTrue();
});

test('numeric route keys do not resolve uuid models', function () {
    $event = Event::factory()->create();

    expect((new Event)->resolveRouteBinding('1'))->toBeNull()
        ->and((new Event)->resolveRouteBinding($event->id)?->is($event))->toBeTrue();
});

test('trashed bindings also skip keys that are not uuids', function () {
    $user = User::factory()->create();
    $user->delete();

    expect((new User)->resolveSoftDeletableRouteBinding('1'))->toBeNull()
        ->and((new User)->resolveSoftDeletableRouteBinding($user->id)?->is($user))->toBeTrue()
        ->and((new User)->resolveSoftDeletableRouteBinding('ada', 'slug'))->toBeNull();
});

test('a consumed or expired challenge is inactive', function () {
    $expired = new LoginChallenge([
        'consumed_at' => null,
        'expires_at' => now()->subMinute(),
    ]);
    $consumed = new LoginChallenge([
        'consumed_at' => now(),
        'expires_at' => now()->addHour(),
    ]);

    expect($expired->isActive())->toBeFalse()
        ->and($consumed->isActive())->toBeFalse();
});
