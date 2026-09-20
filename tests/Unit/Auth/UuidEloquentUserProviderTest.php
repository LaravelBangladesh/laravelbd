<?php

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('invalid login identifiers are ignored', function () {
    $provider = Auth::getProvider();

    expect($provider->retrieveById(1))->toBeNull()
        ->and($provider->retrieveById('1'))->toBeNull()
        ->and($provider->retrieveByToken(1, 'remember'))->toBeNull();
});

test('a uuid login identifier still loads the user', function () {
    $user = User::factory()->create();

    expect(Auth::getProvider()->retrieveById($user->id)?->is($user))->toBeTrue();
});

test('a uuid remember token identifier reaches the parent lookup', function () {
    $user = User::factory()->create();
    $user->forceFill(['remember_token' => 'remember-me'])->save();

    $provider = Auth::getProvider();

    expect($provider->retrieveByToken($user->id, 'remember-me')?->is($user))->toBeTrue()
        ->and($provider->retrieveByToken($user->id, 'wrong-token'))->toBeNull();
});
