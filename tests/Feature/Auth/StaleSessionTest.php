<?php

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Auth;

test('a stale numeric login session is treated as a guest', function () {
    $this->withSession([
        Auth::guard()->getName() => 1,
    ])->get(route('home'))->assertOk();

    $this->assertGuest();
});

test('numeric model ids return not found instead of a database error', function () {
    $this->actingAs(User::factory()->moderator()->create())
        ->get('/admin/events/1')
        ->assertNotFound();
});
