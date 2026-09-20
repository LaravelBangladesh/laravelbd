<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Models\Resource;

final class DeleteResource
{
    public function __invoke(Resource $resource): void
    {
        $resource->delete();
    }
}
