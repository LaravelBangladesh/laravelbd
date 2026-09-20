<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Data\ProfileData;
use App\Domain\Identity\Models\User;

/**
 * Saves the profile and returns true when an email change was requested.
 */
final class UpdateProfile
{
    public function __construct(
        private readonly RequestEmailChange $request,
        private readonly CancelEmailChange $cancel,
    ) {}

    public function __invoke(User $user, ProfileData $data): bool
    {
        $user->update([
            'name' => $data->name,
            'locale' => $data->locale,
        ]);

        if ($data->email !== $user->email) {
            ($this->request)($user, $data->email);

            return true;
        }

        ($this->cancel)($user);

        return false;
    }
}
