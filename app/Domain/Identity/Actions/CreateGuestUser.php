<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Data\GuestUserData;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Adds someone staff bring in, such as a guest speaker, as a member who has
 * not signed in yet. They own the profile once they log in with that email.
 * An email that already has an account returns that user untouched instead
 * of a duplicate, unless that account is deactivated.
 */
final class CreateGuestUser
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(GuestUserData $data, ?UploadedImage $photo = null): User
    {
        $email = Str::lower($data->email);
        $existing = User::query()->withTrashed()->where('email', $email)->first();

        if ($existing?->trashed() === true) {
            throw ValidationException::withMessages([
                'email' => __('admin.email_deactivated'),
            ]);
        }

        if ($existing !== null) {
            return $existing;
        }

        $user = new User;
        $user->fill([
            'name' => $data->name,
            'email' => $email,
            'role' => UserRole::Member,
            'locale' => config('app.locale'),
            'title' => $data->title,
            'company' => $data->company,
        ]);
        $user->refreshSlug();

        if ($photo !== null) {
            $user->photo_path = $this->images->put($photo->contents, $photo->name, 'directory');
        }

        $user->save();

        return $user;
    }
}
