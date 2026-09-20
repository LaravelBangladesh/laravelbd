<?php

namespace App\Domain\Cfp\Data;

use App\Domain\Cfp\Enums\ProposalStatus;

final readonly class ProposalReviewData
{
    public function __construct(
        public ProposalStatus $status,
        public ?string $notes,
        public string $eventId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        $notes = $data['notes'] ?? null;

        return new self(
            status: ProposalStatus::from((string) $data['status']),
            notes: is_string($notes) ? $notes : null,
            eventId: (string) $data['event_id'],
        );
    }
}
