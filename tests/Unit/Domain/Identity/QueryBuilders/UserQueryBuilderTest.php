<?php

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('directory scopes split users by their directory status', function () {
    $hidden = User::factory()->create();
    $pending = User::factory()->pendingInDirectory()->create();
    $listed = User::factory()->listedInDirectory()->create();

    expect(User::query()->listedInDirectory()->pluck('id')->all())->toBe([$listed->id])
        ->and(User::query()->pendingDirectory()->pluck('id')->all())->toBe([$pending->id])
        ->and(User::query()->inDirectory()->pluck('id')->all())
        ->toContain($pending->id, $listed->id)
        ->not->toContain($hidden->id);
});

test('alphabetical orders users by name ascending', function () {
    User::factory()->create(['name' => 'Zara']);
    User::factory()->create(['name' => 'Anik']);

    expect(User::query()->alphabetical()->pluck('name')->all())->toBe(['Anik', 'Zara']);
});
