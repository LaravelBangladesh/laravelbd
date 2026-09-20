<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\StoreEventMediaRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\DeleteEventMedium;
use App\Domain\Events\Actions\StoreEventMedium;
use App\Domain\Events\Data\EventMediumData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;

class EventMediaController extends Controller
{
    public function store(StoreEventMediaRequest $request, Event $event, StoreEventMedium $storeMedium): RedirectResponse
    {
        $this->authorize('update', $event);

        $storeMedium(
            $event,
            EventMediumData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        return back();
    }

    public function destroy(Event $event, EventMedium $medium, DeleteEventMedium $deleteMedium): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($medium->event_id === $event->id, 404);

        $deleteMedium($medium);

        return back();
    }
}
