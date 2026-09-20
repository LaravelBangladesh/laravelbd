<?php

namespace App\Application\Directory\ViewModels;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;

class DirectoryPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function card(DirectoryListing $listing): array
    {
        return [
            'id' => $listing->id,
            'slug' => $listing->slug,
            'name' => $listing->name,
            'title' => $listing->title,
            'company' => $listing->company,
            'city' => $listing->city,
            'kind' => $listing->kind->value,
            'kind_label' => $listing->kind->label(),
            'photo_url' => $listing->photoUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(DirectoryListing $listing): array
    {
        return [
            ...self::card($listing),
            'meta_description' => MetaDescription::make(
                $listing->localized('bio'),
                self::fallbackDescription($listing),
            ),
            'json_ld' => [
                DirectoryJsonLd::make($listing),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.directory') => route('directory.index'),
                    $listing->name => route('directory.show', $listing->slug),
                ]),
            ],
            'bio' => $listing->localized('bio'),
            'website' => $listing->website,
            'github' => $listing->github,
            'linkedin' => $listing->linkedin,
            'x' => $listing->x,
            'links' => self::links($listing),
        ];
    }

    /**
     * @return list<array{key: string, url: string}>
     */
    public static function links(DirectoryListing $listing): array
    {
        return array_values(array_filter([
            ['key' => 'website', 'url' => self::absoluteUrl($listing->website)],
            ['key' => 'github', 'url' => self::profileUrl($listing->github, 'github.com')],
            ['key' => 'linkedin', 'url' => self::absoluteUrl($listing->linkedin)],
            ['key' => 'x', 'url' => self::profileUrl($listing->x, 'x.com')],
        ], fn (array $link) => $link['url'] !== null));
    }

    public static function absoluteUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return 'https://'.$value;
    }

    public static function profileUrl(?string $value, string $host): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return 'https://'.$host.'/'.ltrim($value, '@/');
    }

    /**
     * @return array<string, mixed>
     */
    public static function form(DirectoryListing $listing): array
    {
        return [
            ...self::detail($listing),
            'bio_en' => $listing->bio_en,
            'bio_bn' => $listing->bio_bn,
            'status' => $listing->status->value,
            'status_label' => $listing->status->label(),
            'is_published' => $listing->isPublished(),
        ];
    }

    /**
     * A listing without a bio still deserves a sentence describing who it is,
     * built from the fields every listing has.
     */
    private static function fallbackDescription(DirectoryListing $listing): string
    {
        return implode(' · ', array_filter([
            $listing->name,
            $listing->title,
            $listing->company,
            $listing->city,
        ], fn (?string $value) => $value !== null && $value !== ''));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function kinds(): array
    {
        return array_map(fn (DirectoryKind $kind) => [
            'value' => $kind->value,
            'label' => $kind->label(),
        ], DirectoryKind::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statuses(): array
    {
        return array_map(fn (DirectoryStatus $status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ], DirectoryStatus::cases());
    }
}
