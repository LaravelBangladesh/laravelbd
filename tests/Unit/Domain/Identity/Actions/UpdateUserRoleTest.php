<?php

use App\Domain\Identity\Actions\UpdateUserRole;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('an admin can promote another user', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $member = User::factory()->create(['role' => UserRole::Member]);

    $updated = (new UpdateUserRole)($member, UserRole::Moderator, $admin);

    expect($updated->fresh()?->role)->toBe(UserRole::Moderator);
});

test('an admin cannot demote themselves', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    (new UpdateUserRole)($admin, UserRole::Member, $admin);
})->throws(ValidationException::class);

test('an admin can reassign their own admin role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $updated = (new UpdateUserRole)($admin, UserRole::Admin, $admin);

    expect($updated->fresh()?->role)->toBe(UserRole::Admin);
});
