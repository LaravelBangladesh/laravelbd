<?php

use App\Application\Directory\ViewModels\DirectoryJsonLd;
use App\Domain\Directory\Models\DirectoryListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a person listing publishes their role, employer and city', function () {
    $listing = DirectoryListing::factory()->published()->create([
        'slug' => 'ada-lovelace',
        'name' => 'Ada Lovelace',
        'title' => 'Principal Engineer',
        'company' => 'Analytical Engines',
        'city' => 'Dhaka',
        'bio_en' => 'Builds Laravel applications in Dhaka.',
        'photo_path' => null,
        'website' => 'ada.test',
        'github' => 'ada',
        'linkedin' => null,
        'x' => null,
    ]);

    $schema = DirectoryJsonLd::make($listing);

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('Person')
        ->and($schema['name'])->toBe('Ada Lovelace')
        ->and($schema['url'])->toBe(route('directory.show', 'ada-lovelace'))
        ->and($schema['jobTitle'])->toBe('Principal Engineer')
        ->and($schema['worksFor'])->toBe([
            '@type' => 'Organization',
            'name' => 'Analytical Engines',
        ])
        ->and($schema['description'])->toBe('Builds Laravel applications in Dhaka.')
        ->and($schema['address'])->toBe([
            '@type' => 'PostalAddress',
            'addressLocality' => 'Dhaka',
            'addressCountry' => 'BD',
        ])
        ->and($schema['sameAs'])->toBe([
            'https://ada.test',
            'https://github.com/ada',
        ]);
});

test('a company listing is published as an organization', function () {
    $listing = DirectoryListing::factory()->company()->published()->create([
        'slug' => 'engines-ltd',
        'name' => 'Engines Ltd',
        'city' => 'Chattogram',
        'bio_en' => null,
        'bio_bn' => null,
        'photo_path' => null,
        'website' => 'https://engines.test',
        'github' => null,
        'linkedin' => null,
        'x' => null,
    ]);

    $schema = DirectoryJsonLd::make($listing);

    expect($schema['@type'])->toBe('Organization')
        ->and($schema['name'])->toBe('Engines Ltd')
        ->and($schema['url'])->toBe(route('directory.show', 'engines-ltd'))
        ->and($schema['sameAs'])->toBe(['https://engines.test'])
        ->and($schema['address']['addressLocality'])->toBe('Chattogram')
        ->and($schema)->not->toHaveKey('jobTitle')
        ->and($schema)->not->toHaveKey('description');
});

test('a company with an uploaded logo publishes it', function () {
    Storage::fake('public');

    $listing = DirectoryListing::factory()->company()->published()->create([
        'photo_path' => 'listings/engines.png',
    ]);

    expect(DirectoryJsonLd::make($listing)['logo'])
        ->toBe($listing->photoUrl());
});

test('a listing without optional fields omits them', function () {
    $listing = DirectoryListing::factory()->published()->create([
        'title' => null,
        'company' => null,
        'city' => null,
        'bio_en' => null,
        'bio_bn' => null,
        'photo_path' => null,
        'website' => null,
        'github' => null,
        'linkedin' => null,
        'x' => null,
    ]);

    $schema = DirectoryJsonLd::make($listing);

    expect($schema)->not->toHaveKey('jobTitle')
        ->and($schema)->not->toHaveKey('worksFor')
        ->and($schema)->not->toHaveKey('address')
        ->and($schema)->not->toHaveKey('sameAs')
        ->and($schema)->not->toHaveKey('description');
});
