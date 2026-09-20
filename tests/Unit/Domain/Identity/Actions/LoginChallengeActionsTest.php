<?php

use App\Domain\Identity\Actions\ConsumeMagicLink;
use App\Domain\Identity\Actions\IssueLoginChallenge;
use App\Domain\Identity\Actions\RequestLoginChallenge;
use App\Domain\Identity\Actions\VerifyLoginCode;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Mail\LoginChallengeMail;
use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('issue stores a challenge and invalidates the previous one', function () {
    $first = app(IssueLoginChallenge::class)('anik@example.com', 'Anik');
    $second = app(IssueLoginChallenge::class)('anik@example.com', 'Anik');

    expect($first['code'])->toHaveLength(6)
        ->and($first['token'])->toHaveLength(64)
        ->and($second['code'])->toHaveLength(6)
        ->and(LoginChallenge::query()->whereNull('consumed_at')->count())->toBe(1);
});

test('request login challenge emails an unknown address', function () {
    Mail::fake();

    app(RequestLoginChallenge::class)('missing@example.com');

    Mail::assertQueued(LoginChallengeMail::class, fn (LoginChallengeMail $mail) => $mail->hasTo('missing@example.com'));
});

test('request login challenge uses the existing user name', function () {
    Mail::fake();

    $user = User::factory()->create(['name' => 'Anik']);

    app(RequestLoginChallenge::class)($user->email);

    Mail::assertQueued(LoginChallengeMail::class, fn (LoginChallengeMail $mail) => $mail->hasTo($user->email));
    expect(LoginChallenge::query()->latest()->first()?->name)->toBe('Anik');
});

test('request login challenge prefers an explicit name', function () {
    Mail::fake();

    app(RequestLoginChallenge::class)('anik@example.com', 'Given Name');

    expect(LoginChallenge::query()->latest()->first()?->name)->toBe('Given Name');
});

test('verify login code signs an existing user in', function () {
    Mail::fake();

    $user = User::factory()->create(['email_verified_at' => null]);
    app(RequestLoginChallenge::class)($user->email);

    $verified = app(VerifyLoginCode::class)($user->email, lastLoginCode());

    expect($verified->id)->toBe($user->id)
        ->and($verified->email_verified_at)->not->toBeNull();
});

test('verify login code creates a member on first sign in', function () {
    Mail::fake();

    app(RequestLoginChallenge::class)('new@example.com', 'New Member');

    $user = app(VerifyLoginCode::class)('new@example.com', lastLoginCode());

    expect($user->email)->toBe('new@example.com')
        ->and($user->name)->toBe('New Member')
        ->and($user->role)->toBe(UserRole::Member)
        ->and($user->exists)->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->fresh()?->email_verified_at)->not->toBeNull();
});

test('a member created by magic link is verified straight away', function () {
    Mail::fake();

    app(RequestLoginChallenge::class)('fresh@example.com', 'Fresh Member');

    $token = (string) str(lastMagicUrl())->afterLast('/')->before('?');
    $user = app(ConsumeMagicLink::class)($token);

    expect($user->fresh()?->email_verified_at)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeTrue();
});

test('verify login code rejects an invalid code', function () {
    Mail::fake();

    $user = User::factory()->create();
    app(RequestLoginChallenge::class)($user->email);

    app(VerifyLoginCode::class)($user->email, '000000');
})->throws(ValidationException::class);

test('verify login code rejects an address without a challenge', function () {
    app(VerifyLoginCode::class)('nobody@example.com', '123456');
})->throws(ValidationException::class);

test('consume magic link signs the user in and closes the challenge', function () {
    Mail::fake();

    $user = User::factory()->create();
    app(RequestLoginChallenge::class)($user->email);

    $token = (string) str(lastMagicUrl())->afterLast('/')->before('?');

    expect(app(ConsumeMagicLink::class)($token)->id)->toBe($user->id)
        ->and(LoginChallenge::query()->whereNull('consumed_at')->count())->toBe(0);
});

test('consume magic link rejects an unknown token', function () {
    app(ConsumeMagicLink::class)('not-a-real-token');
})->throws(ValidationException::class);
