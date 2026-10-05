<?php

namespace App\Domain\Identity\QueryBuilders;

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<User>
 */
class UserQueryBuilder extends Builder
{
    public function listedInDirectory(): self
    {
        return $this->where('directory_status', DirectoryVisibility::Listed);
    }

    public function pendingDirectory(): self
    {
        return $this->where('directory_status', DirectoryVisibility::Pending);
    }

    public function inDirectory(): self
    {
        return $this->whereIn('directory_status', [DirectoryVisibility::Pending, DirectoryVisibility::Listed]);
    }

    public function withRole(UserRole $role): self
    {
        return $this->where('role', $role);
    }

    public function withDirectoryStatus(DirectoryVisibility $status): self
    {
        return $this->where('directory_status', $status);
    }

    /**
     * Speaking is derived, not a role: anyone with an accepted proposal or a
     * place on an event or session roster, staff included.
     */
    public function speakers(): self
    {
        return $this->where(fn (self $query) => $query->speaking());
    }

    public function nonSpeakers(): self
    {
        return $this->whereNot(fn (self $query) => $query->speaking());
    }

    /**
     * Matches the name, email or mobile number. Mobile numbers are stored in
     * E.164, so digits typed without the leading + or spacing still match.
     */
    public function search(string $term): self
    {
        $digits = (string) preg_replace('/\D+/', '', $term);

        return $this->where(fn (self $query) => $query
            ->whereLike('name', "%{$term}%")
            ->orWhereLike('email', "%{$term}%")
            ->orWhereLike('mobile_number', "%{$term}%")
            ->when($digits !== '', fn (self $query) => $query->orWhereLike('mobile_number', "%{$digits}%")));
    }

    public function alphabetical(): self
    {
        return $this->orderBy('name');
    }

    private function speaking(): self
    {
        return $this
            ->whereHas('talkProposals', fn ($proposals) => $proposals->where('status', ProposalStatus::Accepted))
            ->orWhereHas('speakerEvents')
            ->orWhereHas('speakerSessions');
    }
}
