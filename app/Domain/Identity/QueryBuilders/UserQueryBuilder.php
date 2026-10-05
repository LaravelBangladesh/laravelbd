<?php

namespace App\Domain\Identity\QueryBuilders;

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

    public function alphabetical(): self
    {
        return $this->orderBy('name');
    }
}
