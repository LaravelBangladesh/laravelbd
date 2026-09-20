<?php

namespace App\Domain\Cfp\Enums;

enum ProposalStatus: string
{
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => __('cfp.statuses.submitted'),
            self::Accepted => __('cfp.statuses.accepted'),
            self::Rejected => __('cfp.statuses.rejected'),
        };
    }
}
