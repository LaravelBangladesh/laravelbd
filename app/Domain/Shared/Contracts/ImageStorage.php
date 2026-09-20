<?php

namespace App\Domain\Shared\Contracts;

interface ImageStorage
{
    /**
     * Store raw image bytes and return the stored path or reference.
     */
    public function put(string $contents, string $filename, string $directory): string;

    public function url(?string $path): ?string;

    public function delete(?string $path): void;
}
