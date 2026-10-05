<?php

namespace App\Domain\Directory\QueryBuilders;

use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Company>
 */
class CompanyQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this->where('status', DirectoryStatus::Published);
    }

    public function draft(): self
    {
        return $this->where('status', DirectoryStatus::Draft);
    }
}
