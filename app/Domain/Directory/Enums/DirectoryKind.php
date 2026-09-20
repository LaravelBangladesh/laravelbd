<?php

namespace App\Domain\Directory\Enums;

enum DirectoryKind: string
{
    case Person = 'person';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Person => __('directory.kinds.person'),
            self::Company => __('directory.kinds.company'),
        };
    }
}
