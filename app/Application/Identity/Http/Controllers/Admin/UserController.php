<?php

namespace App\Application\Identity\Http\Controllers\Admin;

use App\Application\Identity\Http\Requests\Admin\UpdateUserProfileRequest;
use App\Application\Identity\ViewModels\ProfilePresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\ChangeDirectoryVisibility;
use App\Domain\Identity\Actions\UpdateProfileDetails;
use App\Domain\Identity\Data\ProfileDetailsData;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => User::query()
                ->alphabetical()
                ->get(['id', 'name', 'email', 'mobile_number', 'role', 'locale', 'directory_status', 'created_at'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role->value,
                    'locale' => $user->locale,
                    'directory_status' => $user->directory_status->value,
                    'directory_status_label' => $user->directory_status->label(),
                    'created_at' => $user->created_at?->toDateString(),
                ]),
            'roles' => collect(UserRole::cases())->map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/users/edit', [
            'profile' => ProfilePresenter::form($user),
            'visibilities' => ProfilePresenter::visibilities(),
        ]);
    }

    public function update(
        UpdateUserProfileRequest $request,
        User $user,
        UpdateProfileDetails $updateProfile,
        ChangeDirectoryVisibility $changeVisibility,
    ): RedirectResponse {
        $this->authorize('update', $user);

        $updateProfile(
            $user,
            ProfileDetailsData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        $changeVisibility($user, DirectoryVisibility::from((string) $request->validated('directory_status')));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('admin.profile_updated')]);

        return to_route('admin.users.edit', $user);
    }
}
