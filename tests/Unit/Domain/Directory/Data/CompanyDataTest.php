<?php

use App\Domain\Directory\Data\CompanyData;
use App\Domain\Directory\Enums\DirectoryStatus;

test('from validated maps every field', function () {
    $data = CompanyData::fromValidated([
        'name' => 'Acme',
        'status' => DirectoryStatus::Published->value,
        'title' => 'Software studio',
        'city' => 'Dhaka',
        'bio_en' => 'Bio en',
        'bio_bn' => 'Bio bn',
        'website' => 'https://example.com',
        'github' => 'acme',
        'linkedin' => 'https://linkedin.com/company/acme',
        'x' => '@acme',
    ]);

    expect($data->name)->toBe('Acme')
        ->and($data->status)->toBe(DirectoryStatus::Published)
        ->and($data->title)->toBe('Software studio')
        ->and($data->city)->toBe('Dhaka')
        ->and($data->bioEn)->toBe('Bio en')
        ->and($data->bioBn)->toBe('Bio bn')
        ->and($data->website)->toBe('https://example.com')
        ->and($data->github)->toBe('acme')
        ->and($data->linkedin)->toBe('https://linkedin.com/company/acme')
        ->and($data->x)->toBe('@acme');
});

test('from validated defaults to a draft and nulls blanks', function () {
    $data = CompanyData::fromValidated([
        'name' => 'Acme',
        'title' => '',
        'city' => null,
    ]);

    expect($data->status)->toBe(DirectoryStatus::Draft)
        ->and($data->title)->toBeNull()
        ->and($data->city)->toBeNull()
        ->and($data->bioEn)->toBeNull()
        ->and($data->website)->toBeNull();
});

test('attributes stamps published at when publishing and keeps an existing date', function () {
    $published = now()->subWeek();
    $data = CompanyData::fromValidated([
        'name' => 'Acme',
        'status' => DirectoryStatus::Published->value,
    ]);

    expect($data->attributes(null)['published_at'])->not->toBeNull()
        ->and($data->attributes($published)['published_at'])->toBe($published);
});

test('attributes clears published at for a draft', function () {
    $attributes = CompanyData::fromValidated(['name' => 'Acme'])->attributes(now());

    expect($attributes['published_at'])->toBeNull()
        ->and($attributes['name'])->toBe('Acme')
        ->and($attributes['status'])->toBe(DirectoryStatus::Draft);
});
