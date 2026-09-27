<?php

namespace App\Application\Events\Http\Controllers;

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Models\Event;
use Illuminate\Http\RedirectResponse;

/**
 * Short links point here rather than at the slug, which changes with the
 * title, so a link shared once keeps working.
 */
class EventShortLinkController extends Controller
{
    public function __invoke(Event $event): RedirectResponse
    {
        $this->authorize('view', $event);

        return redirect()->route('events.show', $event->slug, 301);
    }
}
