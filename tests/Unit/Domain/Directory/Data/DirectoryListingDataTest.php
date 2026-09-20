<?php

use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;

test('from validated maps every field', function () {
    $data = DirectoryListingData::fromValidated([
        'name' => 'Anik Rahman',
        'kind' => DirectoryKind::Company->value,
        'status' => DirectoryStatus::Published->value,
        'title' => 'Engineer',
        'company' => 'Acme',
        'city' => 'Dhaka',
        'bio_en' => 'Bio en',
        'bio_bn' => 'Bio bn',
        'website' => 'https://example.com',
        'github' => 'anik',
        'linkedin' => 'https://linkedin.com/in/anik',
        'x' => '@anik',
    ]);

    expect($data->name)->toBe('Anik Rahman')
        ->and($data->kind)->toBe(DirectoryKind::Company)
        ->and($data->status)->toBe(DirectoryStatus::Published)
        ->and($data->title)->toBe('Engineer')
        ->and($data->company)->toBe('Acme')
        ->and($data->city)->toBe('Dhaka')
        ->and($data->bioEn)->toBe('Bio en')
        ->and($data->bioBn)->toBe('Bio bn')
        ->and($data->website)->toBe('https://example.com')
        ->and($data->github)->toBe('anik')
        ->and($data->linkedin)->toBe('https://linkedin.com/in/anik')
        ->and($data->x)->toBe('@anik');
});

test('from validated defaults to a draft person and nulls blanks', function () {
    $data = DirectoryListingData::fromValidated([
        'name' => 'Anik',
        'title' => '',
        'city' => null,
    ]);

    expect($data->kind)->toBe(DirectoryKind::Person)
        ->and($data->status)->toBe(DirectoryStatus::Draft)
        ->and($data->title)->toBeNull()
        ->and($data->city)->toBeNull()
        ->and($data->company)->toBeNull()
        ->and($data->bioEn)->toBeNull()
        ->and($data->bioBn)->toBeNull()
        ->and($data->website)->toBeNull()
        ->and($data->github)->toBeNull()
        ->and($data->linkedin)->toBeNull()
        ->and($data->x)->toBeNull();
});

test('attributes stamps published at when publishing', function () {
    $data = DirectoryListingData::fromValidated([
        'name' => 'Anik',
        'status' => DirectoryStatus::Published->value,
    ]);

    expect($data->attributes(null)['published_at'])->not->toBeNull();
});

test('attributes keeps an existing published date', function () {
    $published = now()->subWeek();

    $data = DirectoryListingData::fromValidated([
        'name' => 'Anik',
        'status' => DirectoryStatus::Published->value,
    ]);

    expect($data->attributes($published)['published_at'])->toBe($published);
});

test('attributes clears published at for a draft', function () {
    $data = DirectoryListingData::fromValidated(['name' => 'Anik']);

    $attributes = $data->attributes(now());

    expect($attributes['published_at'])->toBeNull()
        ->and($attributes['name'])->toBe('Anik')
        ->and($attributes['kind'])->toBe(DirectoryKind::Person)
        ->and($attributes['status'])->toBe(DirectoryStatus::Draft);
});
