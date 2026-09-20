<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\ReorderSessionsRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\ReorderSessions;
use App\Domain\Events\Models\Event;
use Illuminate\Http\RedirectResponse;

class ReorderSessionsController extends Controller
{
    public function __invoke(ReorderSessionsRequest $request, Event $event, ReorderSessions $reorder): RedirectResponse
    {
        $this->authorize('update', $event);

        /** @var list<string> $sessionIds */
        $sessionIds = $request->validated('session_ids');

        $reorder($event, $sessionIds);

        return back();
    }
}
