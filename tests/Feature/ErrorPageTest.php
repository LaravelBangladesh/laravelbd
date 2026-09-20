<?php

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('unknown urls render the custom not found page', function () {
    $this->get('/this-route-was-never-on-the-schedule')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page->component('errors/404'));
});

test('users hitting the admin-only role route without admin access get the forbidden page', function () {
    $staff = User::factory()->create(['role' => UserRole::Moderator]);
    $target = User::factory()->create();

    $this->actingAs($staff)
        ->patch("/admin/users/{$target->id}/role", ['role' => 'admin'])
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page->component('errors/403'));
});

test('too many requests render the custom rate limit page', function () {
    Route::get('/__test/throttled', fn () => abort(429))->middleware('web');

    $this->get('/__test/throttled')
        ->assertStatus(429)
        ->assertInertia(fn (Assert $page) => $page->component('errors/429'));
});

test('server errors render the custom error page', function () {
    Route::get('/__test/broken', fn () => abort(500))->middleware('web');

    $this->get('/__test/broken')
        ->assertStatus(500)
        ->assertInertia(fn (Assert $page) => $page->component('errors/500'));
});

test('maintenance mode renders the custom unavailable page', function () {
    Route::get('/__test/down', fn () => abort(503))->middleware('web');

    $this->get('/__test/down')
        ->assertStatus(503)
        ->assertInertia(fn (Assert $page) => $page->component('errors/503'));
});
