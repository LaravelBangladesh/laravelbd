<?php

use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;

test('the registration page redirects to login', function () {
    $this->get(route('register'))->assertRedirect(route('login'));
    $this->post('/register')->assertRedirect(route('login'));
});

test('unknown emails receive a login code and create a member after verify', function () {
    Mail::fake();

    $this->post(route('login.email'), [
        'email' => 'sumon@example.com',
    ])->assertRedirect(route('login.verify'));

    $this->post(route('login.code'), [
        'email' => 'sumon@example.com',
        'code' => lastLoginCode(),
    ])->assertRedirect(route('account.directory.edit'));

    $user = User::query()->where('email', 'sumon@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user?->name)->toBe('')
        ->and($user?->role)->toBe(UserRole::Member)
        ->and($user?->needsProfile())->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

test('members with a blank name can still log out', function () {
    $user = User::factory()->create(['name' => '']);

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('new members must complete their profile before using the rest of the site', function () {
    $user = User::factory()->create(['name' => '']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('account.directory.edit'));

    $this->actingAs($user)
        ->get(route('account.directory.edit'))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('account.directory.store'), [
            'name' => 'Sumon Selim',
            'company' => 'Laravel Bangladesh',
            'title' => 'Organizer',
            'bio_en' => 'Community organizer.',
        ])
        ->assertRedirect(route('account.directory.edit'));

    $user->refresh();
    $listing = $user->directoryListing;

    expect($user->name)->toBe('Sumon Selim')
        ->and($user->needsProfile())->toBeFalse()
        ->and($listing)->not->toBeNull()
        ->and($listing?->company)->toBe('Laravel Bangladesh')
        ->and($listing?->title)->toBe('Organizer')
        ->and($listing?->bio_en)->toBe('Community organizer.')
        ->and(DirectoryListing::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk();
});
