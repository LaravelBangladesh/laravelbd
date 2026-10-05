<?php

namespace App\Application\Identity\Http\Controllers\Account;

use App\Application\Identity\Http\Requests\Account\UpdateDirectoryVisibilityRequest;
use App\Application\Identity\Http\Requests\Account\UpdateProfileDetailsRequest;
use App\Application\Identity\ViewModels\ProfilePresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\Http\ProfileGate;
use App\Domain\Identity\Actions\ChangeDirectoryVisibility;
use App\Domain\Identity\Actions\UpdateProfileDetails;
use App\Domain\Identity\Data\ProfileDetailsData;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The member's own profile, which is also their directory entry. The routes
 * keep their account.directory names because the profile gate sends members
 * here.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('account/directory', [
            'profile' => ProfilePresenter::form($request->user()),
            'missing' => $request->user()->missingProfileFields(),
            'return_to' => ProfileGate::pending(),
        ]);
    }

    public function update(UpdateProfileDetailsRequest $request, UpdateProfileDetails $updateProfile): RedirectResponse
    {
        $user = $updateProfile(
            $request->user(),
            ProfileDetailsData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('account.directory_saved'),
        ]);

        return ProfileGate::resume($user) ?? to_route('account.directory.edit');
    }

    public function visibility(UpdateDirectoryVisibilityRequest $request, ChangeDirectoryVisibility $changeVisibility): RedirectResponse
    {
        $visibility = DirectoryVisibility::from((string) $request->validated('visibility'));

        $changeVisibility($request->user(), $visibility);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $visibility === DirectoryVisibility::Pending
                ? __('account.directory_requested')
                : __('account.directory_hidden'),
        ]);

        return to_route('account.directory.edit');
    }
}
