<?php

namespace App\Domain\Directory\Data;

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use DateTimeInterface;

final readonly class DirectoryListingData
{
    public function __construct(
        public string $name,
        public DirectoryKind $kind,
        public DirectoryStatus $status,
        public ?string $title,
        public ?string $company,
        public ?string $city,
        public ?string $bioEn,
        public ?string $bioBn,
        public ?string $website,
        public ?string $github,
        public ?string $linkedin,
        public ?string $x,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            kind: DirectoryKind::from((string) ($data['kind'] ?? DirectoryKind::Person->value)),
            status: DirectoryStatus::from((string) ($data['status'] ?? DirectoryStatus::Draft->value)),
            title: self::nullableString($data, 'title'),
            company: self::nullableString($data, 'company'),
            city: self::nullableString($data, 'city'),
            bioEn: self::nullableString($data, 'bio_en'),
            bioBn: self::nullableString($data, 'bio_bn'),
            website: self::nullableString($data, 'website'),
            github: self::nullableString($data, 'github'),
            linkedin: self::nullableString($data, 'linkedin'),
            x: self::nullableString($data, 'x'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(?DateTimeInterface $publishedAt): array
    {
        return [
            'name' => $this->name,
            'kind' => $this->kind,
            'status' => $this->status,
            'title' => $this->title,
            'company' => $this->company,
            'city' => $this->city,
            'bio_en' => $this->bioEn,
            'bio_bn' => $this->bioBn,
            'website' => $this->website,
            'github' => $this->github,
            'linkedin' => $this->linkedin,
            'x' => $this->x,
            'published_at' => $this->status === DirectoryStatus::Published
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
