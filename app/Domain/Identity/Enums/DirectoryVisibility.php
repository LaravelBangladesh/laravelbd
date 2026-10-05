<?php

namespace App\Domain\Identity\Enums;

/**
 * Whether a person appears in the public directory. Members request a listing
 * and can hide themselves at any time; only staff can list them.
 */
enum DirectoryVisibility: string
{
    case Hidden = 'hidden';
    case Pending = 'pending';
    case Listed = 'listed';

    public function label(): string
    {
        return match ($this) {
            self::Hidden => __('directory.visibility.hidden'),
            self::Pending => __('directory.visibility.pending'),
            self::Listed => __('directory.visibility.listed'),
        };
    }
}
