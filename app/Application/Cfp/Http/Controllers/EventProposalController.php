<?php

namespace App\Application\Cfp\Http\Controllers;

use App\Application\Cfp\Http\Requests\StoreEventProposalRequest;
use App\Application\Cfp\ViewModels\ProposalPresenter;
use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\Http\ProfileGate;
use App\Domain\Cfp\Actions\SubmitProposal;
use App\Domain\Cfp\Data\ProposalData;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventProposalController extends Controller
{
    public function create(Request $request, Event $event): Response|RedirectResponse
    {
        if (! $request->user()->hasCompleteProfile()) {
            return ProfileGate::redirect('events.cfp.create', $event->slug);
        }

        $this->authorize('create', [TalkProposal::class, $event]);

        return Inertia::render('events/cfp', [
            'event' => EventPresenter::card($event),
            'kinds' => ProposalPresenter::kinds(),
        ]);
    }

    public function store(StoreEventProposalRequest $request, Event $event, SubmitProposal $submit): RedirectResponse
    {
        if (! $request->user()->hasCompleteProfile()) {
            return ProfileGate::redirect('events.cfp.create', $event->slug);
        }

        $this->authorize('create', [TalkProposal::class, $event]);

        $submit(ProposalData::fromValidated($request->validated()), $event, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('cfp.submitted'),
        ]);

        return to_route('account.edit');
    }
}
