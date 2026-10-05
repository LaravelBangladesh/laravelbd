<?php

namespace App\Application\Directory\Http\Controllers;

use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MarkdownDocument;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryController extends Controller
{
    public function index(Request $request): Response|HttpResponse
    {
        $this->authorize('viewAny', Company::class);

        $kind = $request->enum('kind', DirectoryKind::class);

        $people = $kind === DirectoryKind::Company
            ? collect()
            : User::query()->listedInDirectory()->get();
        $companies = $kind === DirectoryKind::Person
            ? collect()
            : Company::query()->published()->get();

        $listings = $people->toBase()
            ->concat($companies)
            ->sortBy(fn (User|Company $entry) => mb_strtolower($entry->name))
            ->map(fn (User|Company $entry) => DirectoryPresenter::card($entry))
            ->values()
            ->all();

        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond(__('directory.title'), array_map(
                fn (array $listing) => "- [{$listing['name']}](".route('directory.show', $listing['slug']).'): '
                    .implode(' · ', array_filter([$listing['title'], $listing['company'], $listing['city']])),
                $listings,
            ));
        }

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

    /**
     * People and companies share the directory URL space, so a slug is looked
     * up among users first and then among companies.
     */
    public function show(Request $request, string $slug): Response|HttpResponse
    {
        $entry = User::query()->where('slug', $slug)->first()
            ?? Company::query()->where('slug', $slug)->firstOrFail();

        $this->authorize('view', $entry);

        $detail = DirectoryPresenter::detail($entry);

        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond($detail['name'], array_filter([
                $detail['bio'],
                ...array_map(fn (array $link) => "- {$link['key']}: {$link['url']}", $detail['links']),
            ]));
        }

        return Inertia::render('directory/show', [
            'listing' => $detail,
            'is_owner' => $entry instanceof User && $entry->id === $request->user()?->id,
            'is_published' => $entry instanceof User ? $entry->isListed() : $entry->isPublished(),
        ]);
    }
}
