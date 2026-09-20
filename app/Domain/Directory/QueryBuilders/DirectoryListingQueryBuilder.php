<?php

namespace App\Domain\Directory\QueryBuilders;

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<DirectoryListing>
 */
class DirectoryListingQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this->where('status', DirectoryStatus::Published);
    }

    public function draft(): self
    {
        return $this->where('status', DirectoryStatus::Draft);
    }

    public function ofKind(?DirectoryKind $kind): self
    {
        return $kind === null ? $this : $this->where('kind', $kind);
    }

    public function alphabetical(): self
    {
        return $this->orderBy('name');
    }
}
