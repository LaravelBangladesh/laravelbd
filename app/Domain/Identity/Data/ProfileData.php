<?php

namespace App\Domain\Identity\Data;

final readonly class ProfileData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $locale,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            email: strtolower((string) $data['email']),
            locale: (string) $data['locale'],
        );
    }
}
