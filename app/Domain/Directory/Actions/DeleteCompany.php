<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Models\Company;
use App\Domain\Shared\Contracts\ImageStorage;

final class DeleteCompany
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Company $company): void
    {
        $this->images->delete($company->photo_path);
        $company->delete();
    }
}
