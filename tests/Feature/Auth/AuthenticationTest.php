<?php

use App\Domain\Identity\Mail\LoginChallengeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;

test('login screen can be rendered', function () {
    $this->get(route('login'))->assertOk();
});

test('existing users can authenticate with an email code', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.email'), ['email' => $user->email])
        ->assertRedirect(route('login.verify'));

    $this->post(route('login.code'), [
        'email' => $user->email,
        'code' => lastLoginCode(),
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

test('the verify screen renders once a code was requested', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.email'), ['email' => $user->email]);

    $this->get(route('login.verify'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/verify')->where('email', $user->email));
});

test('the verify screen redirects to login without a pending address', function () {
    $this->get(route('login.verify'))->assertRedirect(route('login'));
});

test('magic link get does not authenticate', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.email'), ['email' => $user->email]);

    $this->get(lastMagicUrl())->assertOk();
    $this->assertGuest();
});

test('users can authenticate with a magic link', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.email'), ['email' => $user->email]);

    $this->post(lastMagicUrl())->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});

test('invalid codes are rejected', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('login.email'), ['email' => $user->email]);

    $this->post(route('login.code'), [
        'email' => $user->email,
        'code' => '000000',
    ])->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('unknown emails receive a login code', function () {
    Mail::fake();

    $this->post(route('login.email'), ['email' => 'missing@example.com'])
        ->assertRedirect(route('login.verify'));

    Mail::assertQueued(LoginChallengeMail::class, fn ($mail) => $mail->hasTo('missing@example.com'));
    $this->assertGuest();
});

test('password login is rejected', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
