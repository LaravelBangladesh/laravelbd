<?php

namespace App\Application\Shared\Http\Controllers\Admin;

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\DhakaTime;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $nextEvent = Event::query()->next();

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'upcoming_events' => Event::query()->published()->upcoming()->count(),
                'pending_proposals' => TalkProposal::query()->pending()->count(),
                'draft_listings' => DirectoryListing::query()->draft()->count(),
                'next_event_registrations' => $nextEvent?->registeredCount() ?? 0,
                'members' => User::query()->where('role', UserRole::Member)->count(),
                'staff' => User::query()->whereIn('role', [UserRole::Admin, UserRole::Moderator])->count(),
            ],
            'nextEvent' => $nextEvent === null ? null : [
                'id' => $nextEvent->id,
                'title' => $nextEvent->localized('title'),
                'starts_at' => DhakaTime::display($nextEvent->starts_at),
                'venue_name' => $nextEvent->venue_name,
                'capacity' => $nextEvent->capacity,
                'registered_count' => $nextEvent->registeredCount(),
            ],
            'recentProposals' => TalkProposal::query()
                ->with('submitter')
                ->recent(5)
                ->get()
                ->map(fn (TalkProposal $proposal) => [
                    'id' => $proposal->id,
                    'title' => $proposal->title_en,
                    'status' => $proposal->status->value,
                    'status_label' => $proposal->status->label(),
                    'submitter' => $proposal->submitter?->name,
                ])
                ->all(),
        ]);
    }
}
