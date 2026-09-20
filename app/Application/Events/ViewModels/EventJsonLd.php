<?php

namespace App\Application\Events\ViewModels;

use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\DhakaTime;

final class EventJsonLd
{
    /**
     * Build the schema.org Event describing a published event page.
     *
     * @return array<string, mixed>
     */
    public static function make(Event $event, ?string $image): array
    {
        $url = route('events.show', $event->slug);
        $isOnline = $event->online_url !== null && $event->online_url !== '';
        $isOffline = $event->venue_name !== null && $event->venue_name !== '';

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->localized('title'),
            'description' => MetaDescription::make(
                $event->localized('excerpt'),
                $event->localized('description'),
            ),
            'url' => $url,
            'startDate' => DhakaTime::format($event->starts_at, 'c'),
            'endDate' => DhakaTime::format($event->ends_at, 'c'),
            'eventStatus' => $event->status === EventStatus::Cancelled
                ? 'https://schema.org/EventCancelled'
                : 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => self::attendanceMode($isOffline, $isOnline),
            'organizer' => JsonLd::organizer(),
            'offers' => self::offers($event, $url),
        ];

        $location = self::location($event, $isOffline, $isOnline);

        if ($location !== []) {
            $schema['location'] = count($location) === 1 ? $location[0] : $location;
        }

        if ($image !== null) {
            $schema['image'] = $image;
        }

        $performers = self::performers($event);

        if ($performers !== []) {
            $schema['performer'] = $performers;
        }

        return $schema;
    }

    private static function attendanceMode(bool $isOffline, bool $isOnline): string
    {
        return match (true) {
            $isOffline && $isOnline => 'https://schema.org/MixedEventAttendanceMode',
            $isOnline => 'https://schema.org/OnlineEventAttendanceMode',
            default => 'https://schema.org/OfflineEventAttendanceMode',
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function location(Event $event, bool $isOffline, bool $isOnline): array
    {
        $location = [];

        if ($isOffline) {
            $place = [
                '@type' => 'Place',
                'name' => $event->venue_name,
            ];

            if ($event->venue_address !== null && $event->venue_address !== '') {
                $place['address'] = [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $event->venue_address,
                    'addressCountry' => 'BD',
                ];
            }

            $location[] = $place;
        }

        if ($isOnline) {
            $location[] = [
                '@type' => 'VirtualLocation',
                'url' => $event->online_url,
            ];
        }

        return $location;
    }

    /**
     * @return array<string, mixed>
     */
    private static function offers(Event $event, string $url): array
    {
        return [
            '@type' => 'Offer',
            'price' => 0,
            'priceCurrency' => 'BDT',
            'availability' => $event->isFull()
                ? 'https://schema.org/SoldOut'
                : 'https://schema.org/InStock',
            'url' => $url,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function performers(Event $event): array
    {
        return $event->speakers
            ->map(fn (Speaker $speaker) => self::performer($speaker))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function performer(Speaker $speaker): array
    {
        return array_filter([
            '@type' => 'Person',
            'name' => $speaker->name,
            'jobTitle' => $speaker->title,
            'image' => resolve(ImageStorage::class)->url($speaker->photo_path),
        ], fn (?string $value) => $value !== null && $value !== '');
    }
}
