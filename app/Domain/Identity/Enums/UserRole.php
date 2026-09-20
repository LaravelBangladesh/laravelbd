<?php

namespace App\Domain\Identity\Enums;

enum UserRole: string
{
    case Member = 'member';
    case Moderator = 'moderator';
    case Admin = 'admin';

    public function isStaff(): bool
    {
        return $this !== self::Member;
    }

    public function label(): string
    {
        return match ($this) {
            self::Member => __('roles.member'),
            self::Moderator => __('roles.moderator'),
            self::Admin => __('roles.admin'),
        };
    }
}
