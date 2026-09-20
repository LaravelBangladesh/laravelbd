<?php

namespace App\Application\Directory\ViewModels;

use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Models\DirectoryListing;

final class DirectoryJsonLd
{
    /**
     * Build the schema.org entity describing a directory listing, which is a
     * Person for artisans and an Organization for companies.
     *
     * @return array<string, mixed>
     */
    public static function make(DirectoryListing $listing): array
    {
        $sameAs = array_column(DirectoryPresenter::links($listing), 'url');
        $description = MetaDescription::make($listing->localized('bio'));
        $image = $listing->photoUrl();

        $schema = $listing->kind === DirectoryKind::Company
            ? self::company($listing, $image)
            : self::person($listing, $image);

        if ($description !== '') {
            $schema['description'] = $description;
        }

        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private static function person(DirectoryListing $listing, ?string $image): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $listing->name,
            'url' => route('directory.show', $listing->slug),
        ];

        if ($listing->title !== null && $listing->title !== '') {
            $schema['jobTitle'] = $listing->title;
        }

        if ($listing->company !== null && $listing->company !== '') {
            $schema['worksFor'] = [
                '@type' => 'Organization',
                'name' => $listing->company,
            ];
        }

        if ($image !== null) {
            $schema['image'] = $image;
        }

        if ($listing->city !== null && $listing->city !== '') {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $listing->city,
                'addressCountry' => 'BD',
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private static function company(DirectoryListing $listing, ?string $image): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $listing->name,
            'url' => route('directory.show', $listing->slug),
        ];

        if ($image !== null) {
            $schema['logo'] = $image;
        }

        if ($listing->city !== null && $listing->city !== '') {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $listing->city,
                'addressCountry' => 'BD',
            ];
        }

        return $schema;
    }
}
