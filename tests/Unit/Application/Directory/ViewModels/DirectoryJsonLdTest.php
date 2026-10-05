<?php

use App\Application\Directory\ViewModels\DirectoryJsonLd;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\ProfilePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a person publishes their role, employer and city', function () {
    $user = User::factory()->listedInDirectory()->create([
        'slug' => 'ada-lovelace',
        'name' => 'Ada Lovelace',
        'title' => 'Principal Engineer',
        'company' => 'Analytical Engines',
        'city' => 'Dhaka',
        'bio_en' => 'Builds Laravel applications in Dhaka.',
        'photo_path' => null,
        'website' => 'ada.test',
        'github' => 'ada',
    ]);

    $schema = DirectoryJsonLd::make($user);

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('Person')
        ->and($schema['name'])->toBe('Ada Lovelace')
        ->and($schema['url'])->toBe(route('directory.show', 'ada-lovelace'))
        ->and($schema['image'])->toBe(ProfilePhoto::placeholder())
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
        ])
        ->and(json_encode($schema))->not->toContain(substr((string) $user->mobile_number, 4));
});

test('a company is published as an organization', function () {
    $company = Company::factory()->published()->create([
        'slug' => 'engines-ltd',
        'name' => 'Engines Ltd',
        'city' => 'Chattogram',
        'bio_en' => null,
        'website' => 'https://engines.test',
    ]);

    $schema = DirectoryJsonLd::make($company);

    expect($schema['@type'])->toBe('Organization')
        ->and($schema['name'])->toBe('Engines Ltd')
        ->and($schema['url'])->toBe(route('directory.show', 'engines-ltd'))
        ->and($schema['sameAs'])->toBe(['https://engines.test'])
        ->and($schema['address']['addressLocality'])->toBe('Chattogram')
        ->and($schema)->not->toHaveKey('logo')
        ->and($schema)->not->toHaveKey('jobTitle')
        ->and($schema)->not->toHaveKey('description');
});

test('a company with an uploaded logo publishes it', function () {
    Storage::fake('public');

    $company = Company::factory()->published()->create(['photo_path' => 'directory/engines.png']);

    expect(DirectoryJsonLd::make($company)['logo'])->toBe($company->photoUrl());
});

test('a person without optional fields omits them', function () {
    $user = User::factory()->listedInDirectory()->create([
        'title' => null,
        'company' => null,
        'city' => null,
        'bio_en' => null,
    ]);

    $schema = DirectoryJsonLd::make($user);

    expect($schema)->not->toHaveKey('jobTitle')
        ->and($schema)->not->toHaveKey('worksFor')
        ->and($schema)->not->toHaveKey('address')
        ->and($schema)->not->toHaveKey('sameAs')
        ->and($schema)->not->toHaveKey('description');
});
