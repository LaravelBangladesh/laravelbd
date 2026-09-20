<?php

namespace App\Infrastructure\Images;

use App\Domain\Shared\Contracts\ImageStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class LocalDiskImageStorage implements ImageStorage
{
    public function __construct(private readonly string $disk = 'public') {}

    public function put(string $contents, string $filename, string $directory): string
    {
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$this->extension($filename);

        if (! Storage::disk($this->disk)->put($path, $contents)) {
            throw new RuntimeException('Unable to store the uploaded image.');
        }

        return $path;
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk($this->disk)->delete($path);
    }

    private function extension(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return $extension !== '' ? strtolower($extension) : 'jpg';
    }
}
