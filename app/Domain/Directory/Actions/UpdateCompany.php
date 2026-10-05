<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Data\CompanyData;
use App\Domain\Directory\Models\Company;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class UpdateCompany
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Company $company, CompanyData $data, ?UploadedImage $photo): Company
    {
        $company->fill($data->attributes($company->published_at));
        $company->slug = UniqueSlug::make($data->name, ['companies', 'users'], $company->id);

        if ($photo !== null) {
            $this->images->delete($company->photo_path);
            $company->photo_path = $this->images->put($photo->contents, $photo->name, 'directory');
        }

        $company->save();

        return $company;
    }
}
