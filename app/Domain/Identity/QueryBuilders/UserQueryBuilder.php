<?php

namespace App\Domain\Identity\QueryBuilders;

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Identity\Enums\DirectoryVisibility;
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

    /**
     * Speaking is derived, not a role: anyone with an accepted proposal or a
     * place on an event or session roster, staff included.
     */
    public function speakers(): self
    {
        return $this->where(fn (self $query) => $query
            ->whereHas('talkProposals', fn ($proposals) => $proposals->where('status', ProposalStatus::Accepted))
            ->orWhereHas('speakerEvents')
            ->orWhereHas('speakerSessions'));
    }

    public function alphabetical(): self
    {
        return $this->orderBy('name');
    }
}
