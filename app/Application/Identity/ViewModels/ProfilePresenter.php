<?php

namespace App\Application\Identity\ViewModels;

use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;

/**
 * The profile editor's view of a user. It carries the private mobile number,
 * so it is only ever rendered for the user themself or for staff.
 */
class ProfilePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function form(User $user): array
    {
        return [
            'id' => $user->id,
            'slug' => $user->slug,
            'name' => $user->name,
            'title' => $user->title,
            'company' => $user->company,
            'city' => $user->city,
            'bio_en' => $user->bio_en,
            'bio_bn' => $user->bio_bn,
            'website' => $user->website,
            'github' => $user->github,
            'linkedin' => $user->linkedin,
            'x' => $user->x,
            'photo_url' => $user->photoUrl(),
            'mobile_number' => $user->mobile_number,
            ...self::directory($user),
        ];
    }

    /**
     * @return array{directory_status: string, directory_status_label: string, is_listed: bool}
     */
    public static function directory(User $user): array
    {
        return [
            'directory_status' => $user->directory_status->value,
            'directory_status_label' => $user->directory_status->label(),
            'is_listed' => $user->isListed(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function visibilities(): array
    {
        return array_map(fn (DirectoryVisibility $visibility) => [
            'value' => $visibility->value,
            'label' => $visibility->label(),
        ], DirectoryVisibility::cases());
    }

    /**
     * Every user as a staff-only picker option. The email tells apart people
     * who share a name, so it must never reach a public page.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function staffOptions(): array
    {
        return User::query()
            ->alphabetical()
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => $user->name.' — '.$user->email,
            ])
            ->all();
    }
}
