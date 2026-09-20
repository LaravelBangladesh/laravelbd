<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\AssignSessionSpeakerRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\AttachSessionSpeaker;
use App\Domain\Events\Actions\DetachSessionSpeaker;
use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;

class SessionSpeakerController extends Controller
{
    public function store(AssignSessionSpeakerRequest $request, Event $event, EventSession $eventSession, AttachSessionSpeaker $attach): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($eventSession->event_id === $event->id, 404);

        $speaker = $attach(
            $event,
            $eventSession,
            SessionSpeakerData::fromValidated($request->validated()),
            ImageUpload::from($request, 'speaker_photo'),
        );

        abort_unless($speaker !== null, 422);

        return back();
    }

    public function destroy(Event $event, EventSession $eventSession, Speaker $speaker, DetachSessionSpeaker $detach): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($eventSession->event_id === $event->id, 404);

        $detach($event, $eventSession, $speaker);

        return back();
    }
}
