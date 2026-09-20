<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\StoreSpeakerRequest;
use App\Application\Events\Http\Requests\Admin\UpdateSpeakerRequest;
use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\CreateSpeaker;
use App\Domain\Events\Actions\DeleteSpeaker;
use App\Domain\Events\Actions\UpdateSpeaker;
use App\Domain\Events\Data\SpeakerData;
use App\Domain\Events\Models\Speaker;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SpeakerController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Speaker::class);

        return Inertia::render('admin/speakers/index', [
            'speakers' => Speaker::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Speaker $speaker) => EventPresenter::speaker($speaker))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Speaker::class);

        return Inertia::render('admin/speakers/create');
    }

    public function store(StoreSpeakerRequest $request, CreateSpeaker $createSpeaker): RedirectResponse
    {
        $this->authorize('create', Speaker::class);

        $createSpeaker(
            SpeakerData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        return to_route('admin.speakers.index');
    }

    public function edit(Speaker $speaker): Response
    {
        $this->authorize('update', $speaker);

        return Inertia::render('admin/speakers/edit', [
            'speaker' => [
                ...EventPresenter::speaker($speaker),
                'bio_en' => $speaker->bio_en,
                'bio_bn' => $speaker->bio_bn,
            ],
        ]);
    }

    public function update(UpdateSpeakerRequest $request, Speaker $speaker, UpdateSpeaker $updateSpeaker): RedirectResponse
    {
        $this->authorize('update', $speaker);

        $updateSpeaker(
            $speaker,
            SpeakerData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        return to_route('admin.speakers.index');
    }

    public function destroy(Speaker $speaker, DeleteSpeaker $deleteSpeaker): RedirectResponse
    {
        $this->authorize('delete', $speaker);

        $deleteSpeaker($speaker);

        return to_route('admin.speakers.index');
    }
}
