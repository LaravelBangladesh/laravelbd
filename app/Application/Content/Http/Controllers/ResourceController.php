<?php

namespace App\Application\Content\Http\Controllers;

use App\Application\Content\ViewModels\ResourcePresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MarkdownDocument;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(Request $request): Response|HttpResponse
    {
        $this->authorize('viewAny', Resource::class);

        $kind = $request->enum('kind', ResourceKind::class);

        $resources = Resource::query()
            ->published()
            ->with(['event', 'speaker'])
            ->ofKind($kind)
            ->newestFirst()
            ->get()
            ->map(fn (Resource $resource) => ResourcePresenter::card($resource))
            ->all();

        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond(__('resources.title'), array_map(
                fn (array $resource) => "- [{$resource['title']}](".route('resources.show', $resource['slug']).'): '.$resource['excerpt'],
                $resources,
            ));
        }

        return Inertia::render('resources/index', [
            'json_ld' => [
                JsonLd::collectionPage(
                    __('resources.title'),
                    route('resources.index'),
                    __('resources.lead'),
                ),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.resources') => route('resources.index'),
                ]),
            ],
            'resources' => $resources,
            'kind' => $kind?->value,
            'kinds' => ResourcePresenter::kinds(),
        ]);
    }

    public function show(Request $request, Resource $resource): Response|HttpResponse
    {
        $this->authorize('view', $resource);

        $resource->load(['event', 'speaker']);

        $detail = ResourcePresenter::detail($resource);

        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond($detail['title'], array_filter([
                $detail['excerpt'],
                $detail['description'],
                $detail['url'] ? "[{$detail['title']}]({$detail['url']})" : null,
            ]));
        }

        return Inertia::render('resources/show', [
            'resource' => $detail,
        ]);
    }
}
