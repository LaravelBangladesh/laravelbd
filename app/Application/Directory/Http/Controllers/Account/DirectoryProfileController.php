<?php

namespace App\Application\Directory\Http\Controllers\Account;

use App\Application\Directory\Http\Requests\Account\UpsertDirectoryProfileRequest;
use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\Http\ProfileGate;
use App\Domain\Directory\Actions\CreateOwnDirectoryProfile;
use App\Domain\Directory\Actions\UpdateOwnDirectoryProfile;
use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Models\DirectoryListing;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $listing = $request->user()->directoryListing;

        return Inertia::render('account/directory', [
            'listing' => $listing === null
                ? ['name' => $request->user()->name]
                : DirectoryPresenter::form($listing),
            'missing' => $request->user()->missingProfileFields(),
            'return_to' => ProfileGate::pending(),
        ]);
    }

    public function store(UpsertDirectoryProfileRequest $request, CreateOwnDirectoryProfile $createProfile): RedirectResponse
    {
        if ($request->user()->directoryListing !== null) {
            return to_route('account.directory.edit');
        }

        $this->authorize('create', DirectoryListing::class);

        $createProfile(
            $request->user(),
            DirectoryListingData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('account.directory_pending'),
        ]);

        return ProfileGate::resume($request->user()->load('directoryListing'))
            ?? redirect()->intended(route('account.directory.edit'));
    }

    public function update(UpsertDirectoryProfileRequest $request, UpdateOwnDirectoryProfile $updateProfile): RedirectResponse
    {
        $listing = $request->user()->directoryListing;

        abort_if($listing === null, 404);

        $this->authorize('update', $listing);

        $listing = $updateProfile(
            $request->user(),
            $listing,
            DirectoryListingData::fromValidated([
                ...$request->validated(),
                'kind' => DirectoryKind::Person->value,
                'status' => $listing->status->value,
            ]),
            ImageUpload::from($request, 'photo'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $listing->isPublished()
                ? __('account.directory_saved')
                : __('account.directory_pending'),
        ]);

        return ProfileGate::resume($request->user()->load('directoryListing'))
            ?? to_route('account.directory.edit');
    }
}
