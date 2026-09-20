<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\StoreSessionRequest;
use App\Application\Events\Http\Requests\Admin\UpdateSessionRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\CreateSession;
use App\Domain\Events\Actions\DeleteSession;
use App\Domain\Events\Actions\UpdateSession;
use App\Domain\Events\Data\SessionData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;

class EventSessionController extends Controller
{
    public function store(StoreSessionRequest $request, Event $event, CreateSession $createSession): RedirectResponse
    {
        $this->authorize('update', $event);

        $createSession(
            $event,
            SessionData::fromValidated($request->validated()),
            ImageUpload::from($request, 'speaker_photo'),
        );

        return back();
    }

    public function update(UpdateSessionRequest $request, Event $event, EventSession $eventSession, UpdateSession $updateSession): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($eventSession->event_id === $event->id, 404);

        $updateSession(
            $event,
            $eventSession,
            SessionData::fromValidated($request->validated()),
            ImageUpload::from($request, 'speaker_photo'),
        );

        return back();
    }

    public function destroy(Event $event, EventSession $eventSession, DeleteSession $deleteSession): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($eventSession->event_id === $event->id, 404);

        $deleteSession($event, $eventSession);

        return back();
    }
}
