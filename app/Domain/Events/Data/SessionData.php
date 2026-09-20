<?php

namespace App\Domain\Events\Data;

use App\Domain\Events\Enums\SessionKind;
use App\Domain\Shared\DhakaTime;
use Carbon\CarbonImmutable;

final readonly class SessionData
{
    public function __construct(
        public string $titleEn,
        public ?string $titleBn,
        public ?string $descriptionEn,
        public ?string $descriptionBn,
        public SessionKind $kind,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public ?string $room,
        public ?string $recordingUrl,
        public ?int $sortOrder,
        public SessionSpeakerData $speaker,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        $sortOrder = $data['sort_order'] ?? null;

        return new self(
            titleEn: (string) $data['title_en'],
            titleBn: self::nullableString($data, 'title_bn'),
            descriptionEn: self::nullableString($data, 'description_en'),
            descriptionBn: self::nullableString($data, 'description_bn'),
            kind: SessionKind::from((string) $data['kind']),
            startsAt: DhakaTime::parse((string) $data['starts_at']),
            endsAt: DhakaTime::parse((string) $data['ends_at']),
            room: self::nullableString($data, 'room'),
            recordingUrl: self::nullableString($data, 'recording_url'),
            sortOrder: $sortOrder === null || $sortOrder === '' ? null : (int) $sortOrder,
            speaker: SessionSpeakerData::fromValidated($data),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(int $sortOrder): array
    {
        return [
            'title_en' => $this->titleEn,
            'title_bn' => $this->titleBn,
            'description_en' => $this->descriptionEn,
            'description_bn' => $this->descriptionBn,
            'kind' => $this->kind,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'room' => $this->room,
            'recording_url' => $this->recordingUrl,
            'sort_order' => $this->sortOrder ?? $sortOrder,
        ];
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
