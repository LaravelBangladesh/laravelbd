<?php

namespace App\Application\Directory\ViewModels;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;

/**
 * Public directory entries: people come from users, companies from their own
 * table. Only public profile fields are read, never contact details.
 */
class DirectoryPresenter
{
    public static function kind(User|Company $entry): DirectoryKind
    {
        return $entry instanceof Company ? DirectoryKind::Company : DirectoryKind::Person;
    }

    /**
     * @return array<string, mixed>
     */
    public static function card(User|Company $entry): array
    {
        $kind = self::kind($entry);

        return [
            'id' => $entry->id,
            'slug' => $entry->slug,
            'name' => $entry->name,
            'title' => $entry->title,
            'company' => $entry instanceof User ? $entry->company : null,
            'city' => $entry->city,
            'kind' => $kind->value,
            'kind_label' => $kind->label(),
            'photo_url' => $entry->photoUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(User|Company $entry): array
    {
        return [
            ...self::card($entry),
            'meta_description' => MetaDescription::make(
                $entry->localized('bio'),
                self::fallbackDescription($entry),
            ),
            'json_ld' => [
                DirectoryJsonLd::make($entry),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.directory') => route('directory.index'),
                    $entry->name => route('directory.show', (string) $entry->slug),
                ]),
            ],
            'bio' => $entry->localized('bio'),
            'website' => $entry->website,
            'github' => $entry->github,
            'linkedin' => $entry->linkedin,
            'x' => $entry->x,
            'links' => self::links($entry),
        ];
    }

    /**
     * @return list<array{key: string, url: string}>
     */
    public static function links(User|Company $entry): array
    {
        return array_values(array_filter([
            ['key' => 'website', 'url' => self::absoluteUrl($entry->website)],
            ['key' => 'github', 'url' => self::profileUrl($entry->github, 'github.com')],
            ['key' => 'linkedin', 'url' => self::absoluteUrl($entry->linkedin)],
            ['key' => 'x', 'url' => self::profileUrl($entry->x, 'x.com')],
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
    public static function companyForm(Company $company): array
    {
        return [
            ...self::detail($company),
            'bio_en' => $company->bio_en,
            'bio_bn' => $company->bio_bn,
            'status' => $company->status->value,
            'status_label' => $company->status->label(),
            'is_published' => $company->isPublished(),
        ];
    }

    /**
     * An entry without a bio still deserves a sentence describing who it is,
     * built from the fields every entry has.
     */
    private static function fallbackDescription(User|Company $entry): string
    {
        return implode(' · ', array_filter([
            $entry->name,
            $entry->title,
            $entry instanceof User ? $entry->company : null,
            $entry->city,
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
