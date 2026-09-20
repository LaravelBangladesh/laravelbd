<?php

namespace App\Domain\Cfp\Data;

use App\Domain\Cfp\Enums\ProposalKind;

final readonly class ProposalData
{
    public function __construct(
        public string $titleEn,
        public ?string $titleBn,
        public string $abstractEn,
        public ?string $abstractBn,
        public ProposalKind $kind,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            titleEn: (string) $data['title_en'],
            titleBn: self::nullableString($data, 'title_bn'),
            abstractEn: (string) $data['abstract_en'],
            abstractBn: self::nullableString($data, 'abstract_bn'),
            kind: ProposalKind::from((string) $data['kind']),
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
            'abstract_en' => $this->abstractEn,
            'abstract_bn' => $this->abstractBn,
            'kind' => $this->kind,
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
