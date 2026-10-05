<?php

namespace App\Application\Directory\ViewModels;

use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;

final class DirectoryJsonLd
{
    /**
     * Build the schema.org entity describing a directory entry, which is a
     * Person for artisans and an Organization for companies.
     *
     * @return array<string, mixed>
     */
    public static function make(User|Company $entry): array
    {
        $sameAs = array_column(DirectoryPresenter::links($entry), 'url');
        $description = MetaDescription::make($entry->localized('bio'));

        $schema = $entry instanceof Company
            ? self::company($entry)
            : self::person($entry);

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
    private static function person(User $user): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $user->name,
            'url' => route('directory.show', (string) $user->slug),
            'image' => $user->photoUrl(),
        ];

        if ($user->title !== null && $user->title !== '') {
            $schema['jobTitle'] = $user->title;
        }

        if ($user->company !== null && $user->company !== '') {
            $schema['worksFor'] = [
                '@type' => 'Organization',
                'name' => $user->company,
            ];
        }

        return self::withAddress($schema, $user->city);
    }

    /**
     * @return array<string, mixed>
     */
    private static function company(Company $company): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $company->name,
            'url' => route('directory.show', $company->slug),
        ];

        $logo = $company->photoUrl();

        if ($logo !== null) {
            $schema['logo'] = $logo;
        }

        return self::withAddress($schema, $company->city);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private static function withAddress(array $schema, ?string $city): array
    {
        if ($city !== null && $city !== '') {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $city,
                'addressCountry' => 'BD',
            ];
        }

        return $schema;
    }
}
