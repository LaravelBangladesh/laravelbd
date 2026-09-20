<?php

namespace App\Application\Cfp\Http\Controllers\Admin;

use App\Application\Cfp\Http\Requests\Admin\UpdateTalkProposalRequest;
use App\Application\Cfp\ViewModels\ProposalPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Cfp\Actions\ReviewProposal;
use App\Domain\Cfp\Data\ProposalReviewData;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Events\QueryBuilders\EventQueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TalkProposalController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('manage', TalkProposal::class);

        $eventId = $this->filteredEventId($request);

        return Inertia::render('admin/proposals/index', [
            'proposals' => TalkProposal::query()
                ->with(['event', 'submitter'])
                ->when($eventId, fn ($query) => $query->where('event_id', $eventId))
                ->latest()
                ->get()
                ->map(fn (TalkProposal $proposal) => [
                    ...ProposalPresenter::card($proposal),
                    'submitter' => $proposal->submitter?->name,
                ])
                ->all(),
            'event' => $eventId,
            'events' => $this->filterableEvents(),
        ]);
    }

    public function show(TalkProposal $proposal): Response
    {
        $this->authorize('update', $proposal);

        $proposal->load(['event', 'submitter']);

        return Inertia::render('admin/proposals/show', [
            'proposal' => ProposalPresenter::admin($proposal),
            'statuses' => ProposalPresenter::statuses(),
            'events' => Event::query()
                ->orderByDesc('starts_at')
                ->get()
                ->map(fn (Event $event) => [
                    'value' => $event->id,
                    'label' => $event->localized('title'),
                ])
                ->all(),
        ]);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function filterableEvents(): array
    {
        return Event::query()
            ->where(fn (EventQueryBuilder $query) => $query
                ->where('cfp_enabled', true)
                ->orWhereHas('proposals'))
            ->orderByDesc('starts_at')
            ->get()
            ->map(fn (Event $event) => [
                'value' => $event->id,
                'label' => $event->localized('title'),
            ])
            ->values()
            ->all();
    }

    private function filteredEventId(Request $request): ?string
    {
        $eventId = $request->string('event')->toString();

        return $eventId !== '' && Event::query()->whereKey($eventId)->exists()
            ? $eventId
            : null;
    }

    public function update(UpdateTalkProposalRequest $request, TalkProposal $proposal, ReviewProposal $review): RedirectResponse
    {
        $this->authorize('update', $proposal);

        $review($proposal, ProposalReviewData::fromValidated($request->validated()));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('cfp.reviewed'),
        ]);

        return to_route('admin.proposals.index');
    }
}
