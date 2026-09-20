<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Mail\EmailChangeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

test('account page lists the member submitted and accepted talks', function () {
    $user = User::factory()->create();
    $event = Event::factory()->published()->create(['title_en' => 'Dhaka meetup']);
    TalkProposal::factory()->create([
        'user_id' => $user->id,
        'title_en' => 'Queues in production',
    ]);
    TalkProposal::factory()->accepted()->create([
        'user_id' => $user->id,
        'title_en' => 'HTTP Kernel',
        'event_id' => $event->id,
    ]);
    TalkProposal::factory()->create(['title_en' => 'Someone else']);

    $this->actingAs($user)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/edit')
            ->has('proposals', 2)
            ->where(
                'proposals',
                function ($proposals): bool {
                    $byTitle = collect($proposals)->keyBy('title');

                    return $byTitle['HTTP Kernel']['status'] === 'accepted'
                        && $byTitle['HTTP Kernel']['event']['title'] === 'Dhaka meetup'
                        && $byTitle['Queues in production']['status'] === 'submitted'
                        && $byTitle->has('Someone else') === false;
                },
            ));
});

test('account page includes the current email', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/edit')
            ->where('auth.user.email', $user->email)
            ->where('auth.user.pending_email', null)
            ->where('auth.user.photo_url', asset('images/profile-placeholder.svg')));
});

test('users can update their name without changing email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => 'New Name',
            'email' => 'old@example.com',
            'locale' => 'en',
        ])
        ->assertRedirect();

    expect($user->fresh()?->name)->toBe('New Name')
        ->and($user->fresh()?->email)->toBe('old@example.com');

    Mail::assertNothingQueued();
});

test('changing email sends a confirmation and keeps the current login', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
            'locale' => 'en',
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->email)->toBe('old@example.com')
        ->and($user->pending_email)->toBe('new@example.com');

    Mail::assertQueued(EmailChangeMail::class, fn (EmailChangeMail $mail) => $mail->hasTo('new@example.com'));
});

test('users can confirm an email change with a code', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
            'locale' => 'en',
        ]);

    $this->actingAs($user)
        ->post(route('account.email.code'), ['code' => lastEmailChangeCode()])
        ->assertRedirect(route('account.edit'));

    $user->refresh();

    expect($user->email)->toBe('new@example.com')
        ->and($user->pending_email)->toBeNull();
});

test('users can confirm an email change with a magic link', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
            'locale' => 'en',
        ]);

    $url = lastEmailChangeUrl();

    $this->get($url)->assertOk();
    expect($user->fresh()?->email)->toBe('old@example.com');

    $this->post($url)->assertRedirect(route('account.edit'));

    $user->refresh();

    expect($user->email)->toBe('new@example.com');
    $this->assertAuthenticatedAs($user);
});

test('users cannot take an existing email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
            'locale' => 'en',
        ])
        ->assertSessionHasErrors('email');

    Mail::assertNothingQueued();
    expect($user->fresh()?->email)->toBe('old@example.com');
});

test('users can cancel a pending email change', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch(route('account.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
            'locale' => 'en',
        ]);

    $this->actingAs($user)
        ->post(route('account.email.cancel'))
        ->assertRedirect();

    $user->refresh();

    expect($user->email)->toBe('old@example.com')
        ->and($user->pending_email)->toBeNull();
});
