<?php

namespace App\Application\Directory\Http\Controllers\Admin;

use App\Application\Directory\Http\Requests\Admin\StoreDirectoryListingRequest;
use App\Application\Directory\Http\Requests\Admin\UpdateDirectoryListingRequest;
use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Directory\Actions\CreateDirectoryListing;
use App\Domain\Directory\Actions\DeleteDirectoryListing;
use App\Domain\Directory\Actions\UpdateDirectoryListing;
use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Models\DirectoryListing;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryListingController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage', DirectoryListing::class);

        return Inertia::render('admin/directory/index', [
            'listings' => DirectoryListing::query()
                ->alphabetical()
                ->get()
                ->map(fn (DirectoryListing $listing) => [
                    ...DirectoryPresenter::card($listing),
                    'status' => $listing->status->value,
                    'status_label' => $listing->status->label(),
                ])
                ->all(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DirectoryListing::class);

        return Inertia::render('admin/directory/create', $this->formOptions());
    }

    public function store(StoreDirectoryListingRequest $request, CreateDirectoryListing $createListing): RedirectResponse
    {
        $this->authorize('create', DirectoryListing::class);

        $createListing(
            DirectoryListingData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
            $request->user()?->id,
        );

        return to_route('admin.directory.index');
    }

    public function edit(DirectoryListing $listing): Response
    {
        $this->authorize('update', $listing);

        return Inertia::render('admin/directory/edit', [
            'listing' => DirectoryPresenter::form($listing),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateDirectoryListingRequest $request, DirectoryListing $listing, UpdateDirectoryListing $updateListing): RedirectResponse
    {
        $this->authorize('update', $listing);

        $updateListing(
            $listing,
            DirectoryListingData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        return to_route('admin.directory.index');
    }

    public function destroy(DirectoryListing $listing, DeleteDirectoryListing $deleteListing): RedirectResponse
    {
        $this->authorize('delete', $listing);

        $deleteListing($listing);

        return to_route('admin.directory.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'kinds' => DirectoryPresenter::kinds(),
            'statuses' => DirectoryPresenter::statuses(),
        ];
    }
}
