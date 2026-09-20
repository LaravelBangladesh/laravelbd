<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Cfp\Data\ProposalReviewData;
use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;

final class ReviewProposal
{
    public function __construct(private readonly ScheduleAcceptedProposal $schedule) {}

    public function __invoke(TalkProposal $proposal, ProposalReviewData $data): TalkProposal
    {
        $proposal->fill([
            'status' => $data->status,
            'notes' => $data->notes,
            'event_id' => $data->eventId,
        ]);

        if ($data->status === ProposalStatus::Accepted) {
            ($this->schedule)($proposal, Event::query()->findOrFail($data->eventId));
        } else {
            $proposal->save();
        }

        return $proposal;
    }
}
