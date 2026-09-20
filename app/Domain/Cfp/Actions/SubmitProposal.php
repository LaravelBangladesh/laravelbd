<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Cfp\Data\ProposalData;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Illuminate\Validation\ValidationException;

final class SubmitProposal
{
    public function __invoke(ProposalData $data, Event $event, User $submitter): TalkProposal
    {
        if (! $event->isAcceptingProposals()) {
            throw ValidationException::withMessages([
                'event' => __('cfp.closed'),
            ]);
        }

        if (! $submitter->hasCompleteProfile()) {
            throw ValidationException::withMessages([
                'profile' => __('profile.incomplete'),
            ]);
        }

        $proposal = new TalkProposal;
        $proposal->fill($data->attributes());
        $proposal->status = ProposalStatus::Submitted;
        $proposal->event_id = $event->id;
        $proposal->user_id = $submitter->id;
        $proposal->save();

        return $proposal;
    }
}
