<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Validation\ValidationException;

final class UpdateUserRole
{
    public function __invoke(User $user, UserRole $role, User $actor): User
    {
        if ($user->is($actor) && $role !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'role' => __('admin.cannot_demote_self'),
            ]);
        }

        $user->update(['role' => $role]);

        return $user;
    }
}
