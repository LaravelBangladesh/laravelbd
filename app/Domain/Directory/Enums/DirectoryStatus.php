<?php

namespace App\Domain\Directory\Enums;

enum DirectoryStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('events.status.draft'),
            self::Published => __('events.status.published'),
        };
    }
}
