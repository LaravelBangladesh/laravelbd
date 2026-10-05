<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;

final class ChangeDirectoryVisibility
{
    public function __invoke(User $user, DirectoryVisibility $visibility): User
    {
        $user->fill([
            'directory_status' => $visibility,
            'directory_published_at' => $visibility === DirectoryVisibility::Listed
                ? ($user->directory_published_at ?? now())
                : null,
        ]);
        $user->refreshSlug();
        $user->save();

        return $user;
    }
}
