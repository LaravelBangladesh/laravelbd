<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Data\CompanyData;
use App\Domain\Directory\Models\Company;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class CreateCompany
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(CompanyData $data, ?UploadedImage $photo, ?string $createdBy): Company
    {
        $company = new Company;
        $company->fill($data->attributes(null));
        $company->slug = UniqueSlug::make($data->name, ['companies', 'users']);
        $company->created_by = $createdBy;

        if ($photo !== null) {
            $company->photo_path = $this->images->put($photo->contents, $photo->name, 'directory');
        }

        $company->save();

        return $company;
    }
}
