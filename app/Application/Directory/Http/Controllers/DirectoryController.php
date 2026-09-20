<?php

namespace App\Application\Directory\Http\Controllers;

use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Models\DirectoryListing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DirectoryListing::class);

        $kind = $request->enum('kind', DirectoryKind::class);

        $listings = DirectoryListing::query()
            ->published()
            ->ofKind($kind)
            ->alphabetical()
            ->get()
            ->map(fn (DirectoryListing $listing) => DirectoryPresenter::card($listing))
            ->all();

        return Inertia::render('directory/index', [
            'json_ld' => [
                JsonLd::collectionPage(
                    __('directory.title'),
                    route('directory.index'),
                    __('directory.lead'),
                ),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.directory') => route('directory.index'),
                ]),
            ],
            'listings' => $listings,
            'kind' => $kind?->value,
            'kinds' => DirectoryPresenter::kinds(),
        ]);
    }

    public function show(Request $request, DirectoryListing $listing): Response
    {
        $this->authorize('view', $listing);

        return Inertia::render('directory/show', [
            'listing' => DirectoryPresenter::detail($listing),
            'is_owner' => $listing->user_id !== null && $listing->user_id === $request->user()?->id,
            'is_published' => $listing->isPublished(),
        ]);
    }
}
