<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Data\ResourceData;
use App\Domain\Content\Models\Resource;
use App\Domain\Shared\UniqueSlug;

final class UpdateResource
{
    public function __invoke(Resource $resource, ResourceData $data): Resource
    {
        $resource->fill($data->attributes($resource->published_at));
        $resource->slug = UniqueSlug::make($data->titleEn, 'resources', $resource->id);
        $resource->save();

        return $resource;
    }
}
