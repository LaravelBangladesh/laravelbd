<?php

namespace App\Domain\Identity\Data;

final readonly class GuestUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $title,
        public ?string $company,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            email: (string) $data['email'],
            title: self::nullableString($data, 'title'),
            company: self::nullableString($data, 'company'),
        );
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
