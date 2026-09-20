<?php

namespace App\Domain\Shared;

use Carbon\CarbonImmutable;

class DhakaTime
{
    public const ZONE = 'Asia/Dhaka';

    public static function parse(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value, self::ZONE)->utc();
    }

    public static function format(CarbonImmutable|\DateTimeInterface|null $value, string $format = 'Y-m-d\TH:i'): ?string
    {
        if ($value === null) {
            return null;
        }

        return CarbonImmutable::parse($value)->timezone(self::ZONE)->format($format);
    }

    public static function display(CarbonImmutable|\DateTimeInterface|null $value, string $format = 'd M Y, H:i'): ?string
    {
        if ($value === null) {
            return null;
        }

        return CarbonImmutable::parse($value)->timezone(self::ZONE)->format($format);
    }
}
