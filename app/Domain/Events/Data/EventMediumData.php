<?php

namespace App\Domain\Events\Data;

use App\Domain\Events\Enums\MediaKind;

final readonly class EventMediumData
{
    public function __construct(
        public MediaKind $kind,
        public ?string $embedUrl,
        public ?string $captionEn,
        public ?string $captionBn,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        $kind = MediaKind::from((string) $data['kind']);

        return new self(
            kind: $kind,
            embedUrl: $kind === MediaKind::Video ? (string) ($data['embed_url'] ?? '') : null,
            captionEn: self::nullableString($data, 'caption_en'),
            captionBn: self::nullableString($data, 'caption_bn'),
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
