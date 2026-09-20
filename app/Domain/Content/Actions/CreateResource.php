<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Data\ResourceData;
use App\Domain\Content\Models\Resource;
use App\Domain\Shared\UniqueSlug;

final class CreateResource
{
    public function __invoke(ResourceData $data, ?string $createdBy): Resource
    {
        $resource = new Resource;
        $resource->fill($data->attributes(null));
        $resource->slug = UniqueSlug::make($data->titleEn, 'resources');
        $resource->created_by = $createdBy;
        $resource->save();

        return $resource;
    }
}
