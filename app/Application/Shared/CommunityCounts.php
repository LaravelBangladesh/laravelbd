<?php

namespace App\Application\Shared;

use App\Domain\Directory\Models\Company;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;

class CommunityCounts
{
    /**
     * @return array{events: int, meetups: int, cities: int}
     */
    public static function make(): array
    {
        return [
            'events' => Event::query()->published()->count(),
            'meetups' => Event::query()->published()->where('type', EventType::Meetup)->count(),
            'cities' => self::cities(),
        ];
    }

    public static function speakers(): int
    {
        return User::query()->speakers()->count();
    }

    private static function cities(): int
    {
        return User::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->pluck('city')
            ->merge(
                Company::query()
                    ->published()
                    ->whereNotNull('city')
                    ->where('city', '!=', '')
                    ->pluck('city'),
            )
            ->map(fn (string $city) => mb_strtolower(trim($city)))
            ->filter()
            ->unique()
            ->count();
    }
}
