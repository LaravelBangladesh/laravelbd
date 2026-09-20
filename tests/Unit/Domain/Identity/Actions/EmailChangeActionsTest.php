<?php

use App\Domain\Identity\Actions\CancelEmailChange;
use App\Domain\Identity\Actions\ConsumeEmailChangeLink;
use App\Domain\Identity\Actions\RequestEmailChange;
use App\Domain\Identity\Actions\VerifyEmailChangeCode;
use App\Domain\Identity\Mail\EmailChangeMail;
use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('requesting the same email clears a pending change', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'old@example.com',
        'pending_email' => 'new@example.com',
    ]);

    app(RequestEmailChange::class)($user, 'old@example.com');

    expect($user->fresh()?->pending_email)->toBeNull();
    Mail::assertNothingQueued();
});

test('requesting a taken email is rejected', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    app(RequestEmailChange::class)($user, 'taken@example.com');
})->throws(ValidationException::class);

test('requesting a new email stores a pending address and sends mail', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    app(RequestEmailChange::class)($user, 'NEW@example.com');

    expect($user->fresh()?->pending_email)->toBe('new@example.com');
    Mail::assertQueued(EmailChangeMail::class, fn (EmailChangeMail $mail) => $mail->hasTo('new@example.com'));
});

test('cancel clears the pending email and closes its challenges', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    app(RequestEmailChange::class)($user, 'new@example.com');

    app(CancelEmailChange::class)($user);

    expect($user->fresh()?->pending_email)->toBeNull()
        ->and(LoginChallenge::query()->whereNull('consumed_at')->count())->toBe(0);
});

test('cancel is a no-op without a pending email', function () {
    $user = User::factory()->create(['pending_email' => null]);

    app(CancelEmailChange::class)($user);

    expect($user->fresh()?->pending_email)->toBeNull();
});

test('verify code applies the pending email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    app(RequestEmailChange::class)($user, 'new@example.com');

    $updated = app(VerifyEmailChangeCode::class)($user, lastEmailChangeCode());

    expect($updated->email)->toBe('new@example.com')
        ->and($updated->pending_email)->toBeNull();
});

test('verify code without a pending email is rejected', function () {
    $user = User::factory()->create(['pending_email' => null]);

    app(VerifyEmailChangeCode::class)($user, '123456');
})->throws(ValidationException::class);

test('verify code rejects a wrong code', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    app(RequestEmailChange::class)($user, 'new@example.com');

    app(VerifyEmailChangeCode::class)($user, '000000');
})->throws(ValidationException::class);

test('verify code is rejected when the address was taken meanwhile', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    app(RequestEmailChange::class)($user, 'new@example.com');
    $code = lastEmailChangeCode();

    User::factory()->create(['email' => 'new@example.com']);

    app(VerifyEmailChangeCode::class)($user, $code);
})->throws(ValidationException::class);

test('consume link applies the pending email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    app(RequestEmailChange::class)($user, 'new@example.com');

    $token = (string) str(lastEmailChangeUrl())->afterLast('/')->before('?');

    expect(app(ConsumeEmailChangeLink::class)($token)->email)->toBe('new@example.com');
});

test('consume link rejects an unknown token', function () {
    app(ConsumeEmailChangeLink::class)('not-a-real-token');
})->throws(ValidationException::class);

test('consume link rejects a challenge with no pending user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    app(RequestEmailChange::class)($user, 'new@example.com');
    $token = (string) str(lastEmailChangeUrl())->afterLast('/')->before('?');

    $user->forceFill(['pending_email' => null])->save();

    app(ConsumeEmailChangeLink::class)($token);
})->throws(ValidationException::class);
