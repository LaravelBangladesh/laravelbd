<?php

namespace App\Domain\Identity;

class ProfilePhoto
{
    public static function placeholder(): string
    {
        return asset('images/profile-placeholder.svg');
    }
}
