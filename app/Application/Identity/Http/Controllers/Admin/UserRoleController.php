<?php

namespace App\Application\Identity\Http\Controllers\Admin;

use App\Application\Identity\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\UpdateUserRole;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UserRoleController extends Controller
{
    public function __invoke(UpdateUserRoleRequest $request, User $user, UpdateUserRole $updateUserRole): RedirectResponse
    {
        $role = $request->validated('role');

        $updateUserRole(
            $user,
            $role instanceof UserRole ? $role : UserRole::from((string) $role),
            $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('admin.role_updated')]);

        return back();
    }
}
