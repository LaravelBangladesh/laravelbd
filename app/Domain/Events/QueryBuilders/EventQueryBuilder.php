<?php

namespace App\Domain\Events\QueryBuilders;

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Models\Event;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Event>
 */
class EventQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this->where('status', EventStatus::Published);
    }

    public function upcoming(): self
    {
        return $this->where('ends_at', '>=', now())->orderByDesc('starts_at');
    }

    public function past(): self
    {
        return $this->where('ends_at', '<', now())->orderByDesc('starts_at');
    }

    public function acceptingProposals(): self
    {
        return $this->published()
            ->where('cfp_enabled', true)
            ->where(fn (self $query) => $query
                ->whereNull('cfp_opens_at')
                ->orWhere('cfp_opens_at', '<=', now()))
            ->where(fn (self $query) => $query
                ->whereNull('cfp_closes_at')
                ->orWhere('cfp_closes_at', '>', now()));
    }

    public function next(): ?Event
    {
        return $this->published()->upcoming()->reorder('starts_at')->first();
    }
}
