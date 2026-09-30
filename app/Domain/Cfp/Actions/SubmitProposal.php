<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Cfp\Data\ProposalData;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Events\QuestionAnswers;
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

        $questions = $event->cfp_questions ?? [];
        $values = QuestionAnswers::validate($questions, $data->answers);

        $proposal = new TalkProposal;
        $proposal->fill($data->attributes());
        // Snapshot each answered question so later edits to the event's
        // questions never change what the speaker was asked.
        $proposal->answers = array_values(array_map(
            fn (array $question) => [...$question, 'value' => $values[$question['id']]],
            array_filter($questions, fn (array $question) => isset($values[$question['id']])),
        ));
        $proposal->status = ProposalStatus::Submitted;
        $proposal->event_id = $event->id;
        $proposal->user_id = $submitter->id;
        $proposal->save();

        return $proposal;
    }
}
