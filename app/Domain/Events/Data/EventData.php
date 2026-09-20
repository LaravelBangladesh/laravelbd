<?php

namespace App\Domain\Events\Data;

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Shared\DhakaTime;
use Carbon\CarbonImmutable;

final readonly class EventData
{
    public function __construct(
        public string $titleEn,
        public ?string $titleBn,
        public ?string $excerptEn,
        public ?string $excerptBn,
        public ?string $descriptionEn,
        public ?string $descriptionBn,
        public EventType $type,
        public EventStatus $status,
        public ?string $venueName,
        public ?string $venueAddress,
        public ?string $venueMapUrl,
        public ?string $onlineUrl,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public ?int $capacity,
        public bool $registrationEnabled,
        public bool $cfpEnabled,
        public ?CarbonImmutable $cfpOpensAt,
        public ?CarbonImmutable $cfpClosesAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            titleEn: (string) $data['title_en'],
            titleBn: self::nullableString($data, 'title_bn'),
            excerptEn: self::nullableString($data, 'excerpt_en'),
            excerptBn: self::nullableString($data, 'excerpt_bn'),
            descriptionEn: self::nullableString($data, 'description_en'),
            descriptionBn: self::nullableString($data, 'description_bn'),
            type: EventType::from((string) $data['type']),
            status: EventStatus::from((string) $data['status']),
            venueName: self::nullableString($data, 'venue_name'),
            venueAddress: self::nullableString($data, 'venue_address'),
            venueMapUrl: self::nullableString($data, 'venue_map_url'),
            onlineUrl: self::nullableString($data, 'online_url'),
            startsAt: DhakaTime::parse((string) $data['starts_at']),
            endsAt: DhakaTime::parse((string) $data['ends_at']),
            capacity: self::nullableInt($data, 'capacity'),
            registrationEnabled: (bool) ($data['registration_enabled'] ?? false),
            cfpEnabled: (bool) ($data['cfp_enabled'] ?? false),
            cfpOpensAt: self::nullableDate($data, 'cfp_opens_at'),
            cfpClosesAt: self::nullableDate($data, 'cfp_closes_at'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return [
            'title_en' => $this->titleEn,
            'title_bn' => $this->titleBn,
            'excerpt_en' => $this->excerptEn,
            'excerpt_bn' => $this->excerptBn,
            'description_en' => $this->descriptionEn,
            'description_bn' => $this->descriptionBn,
            'type' => $this->type,
            'status' => $this->status,
            'venue_name' => $this->venueName,
            'venue_address' => $this->venueAddress,
            'venue_map_url' => $this->venueMapUrl,
            'online_url' => $this->onlineUrl,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'capacity' => $this->capacity,
            'registration_enabled' => $this->registrationEnabled,
            'cfp_enabled' => $this->cfpEnabled,
            'cfp_opens_at' => $this->cfpOpensAt,
            'cfp_closes_at' => $this->cfpClosesAt,
            'published_at' => $this->status === EventStatus::Published ? now() : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function nullableDate(array $data, string $key): ?CarbonImmutable
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? DhakaTime::parse($value) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function nullableInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }
}
