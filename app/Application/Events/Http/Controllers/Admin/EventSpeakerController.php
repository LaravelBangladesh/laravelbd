<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\AttachSpeakerRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\AttachEventSpeaker;
use App\Domain\Events\Actions\DetachEventSpeaker;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use Illuminate\Http\RedirectResponse;

class EventSpeakerController extends Controller
{
    public function store(AttachSpeakerRequest $request, Event $event, AttachEventSpeaker $attach): RedirectResponse
    {
        $this->authorize('update', $event);

        $attach(
            $event,
            $request->string('speaker_id')->toString(),
            $request->string('role')->toString(),
        );

        return back();
    }

    public function destroy(Event $event, Speaker $speaker, DetachEventSpeaker $detach): RedirectResponse
    {
        $this->authorize('update', $event);

        $detach($event, $speaker);

        return back();
    }
}
