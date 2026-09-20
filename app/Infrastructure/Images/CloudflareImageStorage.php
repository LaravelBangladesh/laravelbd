<?php

namespace App\Infrastructure\Images;

use App\Domain\Shared\Contracts\ImageStorage;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareImageStorage implements ImageStorage
{
    public function __construct(private readonly ImageStorage $fallback) {}

    public function put(string $contents, string $filename, string $directory): string
    {
        if (! $this->configured()) {
            return $this->fallback->put($contents, $filename, $directory);
        }

        $response = Http::withToken((string) config('cloudflare.images.api_token'))
            ->attach('file', $contents, $filename)
            ->post($this->accountUrl())
            ->throw()
            ->json();

        $id = data_get($response, 'result.id');

        if (! is_string($id) || $id === '') {
            throw new RuntimeException('Cloudflare Images did not return an image id.');
        }

        return 'cf:'.$id;
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (! str_starts_with($path, 'cf:')) {
            return $this->fallback->url($path);
        }

        $delivery = rtrim((string) config('cloudflare.images.delivery_url'), '/');

        return $delivery.'/'.substr($path, 3).'/public';
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (! str_starts_with($path, 'cf:')) {
            $this->fallback->delete($path);

            return;
        }

        if (! $this->configured()) {
            return;
        }

        Http::withToken((string) config('cloudflare.images.api_token'))
            ->delete($this->accountUrl().'/'.substr($path, 3))
            ->throw();
    }

    private function configured(): bool
    {
        return filled(config('cloudflare.images.account_id'))
            && filled(config('cloudflare.images.api_token'))
            && filled(config('cloudflare.images.delivery_url'));
    }

    private function accountUrl(): string
    {
        return 'https://api.cloudflare.com/client/v4/accounts/'.config('cloudflare.images.account_id').'/images/v1';
    }
}
