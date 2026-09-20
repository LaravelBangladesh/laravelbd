<?php

namespace App\Domain\Events\Enums;

enum MediaKind: string
{
    case Photo = 'photo';
    case Video = 'video';
}
