<?php

namespace App\Domain\Shared\Data;

final readonly class UploadedImage
{
    public function __construct(
        public string $contents,
        public string $name,
    ) {}
}
