<?php

namespace App\Application\Content\Http\Controllers;

use App\Application\Content\ViewModels\ResourcePresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Models\Resource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(Request $request): Response
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

    public function show(Resource $resource): Response
    {
        $this->authorize('view', $resource);

        $resource->load(['event', 'speaker']);

        return Inertia::render('resources/show', [
            'resource' => ResourcePresenter::detail($resource),
        ]);
    }
}
