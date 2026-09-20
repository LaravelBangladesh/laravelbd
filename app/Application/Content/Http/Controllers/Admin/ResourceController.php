<?php

namespace App\Application\Content\Http\Controllers\Admin;

use App\Application\Content\Http\Requests\Admin\StoreResourceRequest;
use App\Application\Content\Http\Requests\Admin\UpdateResourceRequest;
use App\Application\Content\ViewModels\ResourcePresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Content\Actions\CreateResource;
use App\Domain\Content\Actions\DeleteResource;
use App\Domain\Content\Actions\UpdateResource;
use App\Domain\Content\Data\ResourceData;
use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage', Resource::class);

        return Inertia::render('admin/resources/index', [
            'resources' => Resource::query()
                ->with(['event', 'speaker'])
                ->latest()
                ->get()
                ->map(fn (Resource $resource) => [
                    ...ResourcePresenter::card($resource),
                    'status' => $resource->status->value,
                    'status_label' => $resource->status->label(),
                ])
                ->all(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Resource::class);

        return Inertia::render('admin/resources/create', $this->formOptions());
    }

    public function store(StoreResourceRequest $request, CreateResource $createResource): RedirectResponse
    {
        $this->authorize('create', Resource::class);

        $createResource(
            ResourceData::fromValidated($request->validated()),
            $request->user()?->id,
        );

        return to_route('admin.resources.index');
    }

    public function edit(Resource $resource): Response
    {
        $this->authorize('update', $resource);

        return Inertia::render('admin/resources/edit', [
            'resource' => ResourcePresenter::form($resource),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateResourceRequest $request, Resource $resource, UpdateResource $updateResource): RedirectResponse
    {
        $this->authorize('update', $resource);

        $updateResource($resource, ResourceData::fromValidated($request->validated()));

        return to_route('admin.resources.index');
    }

    public function destroy(Resource $resource, DeleteResource $deleteResource): RedirectResponse
    {
        $this->authorize('delete', $resource);

        $deleteResource($resource);

        return to_route('admin.resources.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'kinds' => ResourcePresenter::kinds(),
            'statuses' => ResourcePresenter::statuses(),
            'events' => Event::query()
                ->orderByDesc('starts_at')
                ->get(['id', 'title_en'])
                ->map(fn (Event $event) => [
                    'value' => $event->id,
                    'label' => $event->title_en,
                ])
                ->all(),
            'speakers' => Speaker::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Speaker $speaker) => [
                    'value' => $speaker->id,
                    'label' => $speaker->name,
                ])
                ->all(),
        ];
    }
}
