<?php

namespace App\Domain\Events\Data;

use App\Domain\Events\Enums\SpeakerRole;

final readonly class SessionSpeakerData
{
    public function __construct(
        public ?string $source,
        public ?string $speakerId,
        public string $role,
        public ?string $name,
        public ?string $title,
        public ?string $company,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        $role = self::nullableString($data, 'speaker_role');

        return new self(
            source: self::nullableString($data, 'speaker_source'),
            speakerId: self::nullableString($data, 'speaker_id'),
            role: $role !== null && $role !== '' ? $role : SpeakerRole::Speaker->value,
            name: self::nullableString($data, 'speaker_name'),
            title: self::nullableString($data, 'speaker_title'),
            company: self::nullableString($data, 'speaker_company'),
        );
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
