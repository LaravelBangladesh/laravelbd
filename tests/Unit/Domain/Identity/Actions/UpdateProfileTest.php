<?php

use App\Domain\Identity\Actions\UpdateProfile;
use App\Domain\Identity\Data\ProfileData;
use App\Domain\Identity\Mail\EmailChangeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('update profile saves the name and locale without an email change', function () {
    Mail::fake();

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'anik@example.com',
        'locale' => 'en',
        'pending_email' => 'stale@example.com',
    ]);

    $requested = app(UpdateProfile::class)($user, ProfileData::fromValidated([
        'name' => 'New Name',
        'email' => 'anik@example.com',
        'locale' => 'bn',
    ]));

    expect($requested)->toBeFalse()
        ->and($user->fresh()?->name)->toBe('New Name')
        ->and($user->fresh()?->locale)->toBe('bn')
        ->and($user->fresh()?->pending_email)->toBeNull();

    Mail::assertNothingQueued();
});

test('update profile requests a challenge for a new email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'anik@example.com']);

    $requested = app(UpdateProfile::class)($user, ProfileData::fromValidated([
        'name' => 'Anik',
        'email' => 'new@example.com',
        'locale' => 'en',
    ]));

    expect($requested)->toBeTrue()
        ->and($user->fresh()?->pending_email)->toBe('new@example.com');

    Mail::assertQueued(EmailChangeMail::class);
});
