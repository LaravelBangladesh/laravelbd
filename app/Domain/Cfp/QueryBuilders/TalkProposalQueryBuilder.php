<?php

namespace App\Domain\Cfp\QueryBuilders;

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<TalkProposal>
 */
class TalkProposalQueryBuilder extends Builder
{
    public function accepted(): self
    {
        return $this->where('status', ProposalStatus::Accepted);
    }

    public function pending(): self
    {
        return $this->where('status', ProposalStatus::Submitted);
    }

    public function recent(int $limit): self
    {
        return $this->latest()->limit($limit);
    }
}
