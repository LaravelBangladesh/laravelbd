<?php

namespace App\Domain\Cfp\Policies;

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;

class TalkProposalPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, TalkProposal $proposal): bool
    {
        return $proposal->isAccepted()
            || $user?->isStaff() === true
            || $user?->id === $proposal->user_id;
    }

    public function create(User $user, Event $event): bool
    {
        return $event->isAcceptingProposals() && $user->hasCompleteProfile();
    }

    public function update(User $user, TalkProposal $proposal): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, TalkProposal $proposal): bool
    {
        return $user->isStaff();
    }

    public function manage(User $user): bool
    {
        return $user->isStaff();
    }
}
