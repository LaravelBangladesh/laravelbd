<?php

namespace App\Domain\Content\Data;

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use DateTimeInterface;

final readonly class ResourceData
{
    public function __construct(
        public string $titleEn,
        public ?string $titleBn,
        public ?string $excerptEn,
        public ?string $excerptBn,
        public ?string $descriptionEn,
        public ?string $descriptionBn,
        public ResourceKind $kind,
        public ResourceStatus $status,
        public ?string $url,
        public ?string $embedUrl,
        public ?string $eventId,
        public ?string $speakerId,
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
            kind: ResourceKind::from((string) $data['kind']),
            status: ResourceStatus::from((string) $data['status']),
            url: self::nullableString($data, 'url'),
            embedUrl: self::nullableString($data, 'embed_url'),
            eventId: self::nullableString($data, 'event_id'),
            speakerId: self::nullableString($data, 'speaker_id'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(?DateTimeInterface $publishedAt): array
    {
        return [
            'title_en' => $this->titleEn,
            'title_bn' => $this->titleBn,
            'excerpt_en' => $this->excerptEn,
            'excerpt_bn' => $this->excerptBn,
            'description_en' => $this->descriptionEn,
            'description_bn' => $this->descriptionBn,
            'kind' => $this->kind,
            'status' => $this->status,
            'url' => $this->url,
            'embed_url' => $this->embedUrl,
            'event_id' => $this->eventId,
            'speaker_id' => $this->speakerId,
            'published_at' => $this->status === ResourceStatus::Published
                ? ($publishedAt ?? now())
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
