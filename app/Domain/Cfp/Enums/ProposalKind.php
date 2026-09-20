<?php

namespace App\Domain\Cfp\Enums;

use App\Domain\Events\Enums\SessionKind;

enum ProposalKind: string
{
    case Talk = 'talk';
    case Workshop = 'workshop';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Talk => __('cfp.kinds.talk'),
            self::Workshop => __('cfp.kinds.workshop'),
            self::Other => __('cfp.kinds.other'),
        };
    }

    public function sessionKind(): SessionKind
    {
        return match ($this) {
            self::Talk => SessionKind::Talk,
            self::Workshop => SessionKind::Workshop,
            self::Other => SessionKind::Other,
        };
    }
}
