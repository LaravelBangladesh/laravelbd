<?php

use App\Domain\Cfp\Data\ProposalReviewData;
use App\Domain\Cfp\Enums\ProposalStatus;

test('builds review data from validated input', function () {
    $accepted = ProposalReviewData::fromValidated([
        'status' => ProposalStatus::Accepted->value,
        'notes' => 'Great fit.',
        'event_id' => 'event-uuid',
    ]);

    $rejected = ProposalReviewData::fromValidated([
        'status' => ProposalStatus::Rejected->value,
        'notes' => null,
        'event_id' => 'other-uuid',
    ]);

    expect($accepted->status)->toBe(ProposalStatus::Accepted)
        ->and($accepted->notes)->toBe('Great fit.')
        ->and($accepted->eventId)->toBe('event-uuid')
        ->and($rejected->status)->toBe(ProposalStatus::Rejected)
        ->and($rejected->notes)->toBeNull()
        ->and($rejected->eventId)->toBe('other-uuid');
});
