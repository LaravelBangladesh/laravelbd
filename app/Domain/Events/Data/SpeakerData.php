<?php

namespace App\Domain\Events\Data;

final readonly class SpeakerData
{
    public function __construct(
        public string $name,
        public ?string $title,
        public ?string $company,
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
            title: self::nullableString($data, 'title'),
            company: self::nullableString($data, 'company'),
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
    public function attributes(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'company' => $this->company,
            'bio_en' => $this->bioEn,
            'bio_bn' => $this->bioBn,
            'website' => $this->website,
            'github' => $this->github,
            'linkedin' => $this->linkedin,
            'x' => $this->x,
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
