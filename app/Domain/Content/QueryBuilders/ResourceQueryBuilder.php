<?php

namespace App\Domain\Content\QueryBuilders;

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<\App\Domain\Content\Models\Resource>
 */
class ResourceQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this->where('status', ResourceStatus::Published);
    }

    public function ofKind(?ResourceKind $kind): self
    {
        return $kind === null ? $this : $this->where('kind', $kind);
    }

    public function newestFirst(): self
    {
        return $this->latest('published_at')->latest();
    }
}
