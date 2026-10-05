<?php

namespace App\Domain\Directory\Policies;

use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;

class CompanyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Company $company): bool
    {
        return $company->isPublished() || $user?->isStaff() === true;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isStaff();
    }

    public function manage(User $user): bool
    {
        return $user->isStaff();
    }
}
