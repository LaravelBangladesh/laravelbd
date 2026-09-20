<?php

namespace App\Domain\Events\Data;

use App\Domain\Events\Enums\QuestionKind;

final readonly class EventQuestionData
{
    /**
     * @param  list<string>|null  $options
     */
    public function __construct(
        public QuestionKind $kind,
        public string $labelEn,
        public ?string $labelBn,
        public ?string $helpEn,
        public ?string $helpBn,
        public ?array $options,
        public bool $required,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        $kind = QuestionKind::from((string) $data['kind']);

        return new self(
            kind: $kind,
            labelEn: (string) $data['label_en'],
            labelBn: self::nullableString($data, 'label_bn'),
            helpEn: self::nullableString($data, 'help_en'),
            helpBn: self::nullableString($data, 'help_bn'),
            options: $kind->isChoice() ? self::options($data) : null,
            required: (bool) ($data['required'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(int $position): array
    {
        return [
            'kind' => $this->kind,
            'label_en' => $this->labelEn,
            'label_bn' => $this->labelBn,
            'help_en' => $this->helpEn,
            'help_bn' => $this->helpBn,
            'options' => $this->options,
            'required' => $this->required,
            'position' => $position,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private static function options(array $data): array
    {
        $value = $data['options'] ?? [];

        return array_values(array_map(
            fn (mixed $option): string => trim((string) $option),
            is_array($value) ? $value : [],
        ));
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
