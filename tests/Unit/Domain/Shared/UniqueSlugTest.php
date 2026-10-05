<?php

use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\UniqueSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('slugs a title and suffixes collisions', function () {
    Company::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace']);

    expect(UniqueSlug::make('Ada Lovelace', 'companies'))->toBe('ada-lovelace-2');
});

test('ignores the current record when renaming', function () {
    $company = Company::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace']);

    expect(UniqueSlug::make('Ada Lovelace', 'companies', $company->id))->toBe('ada-lovelace');
});

test('falls back when a title has no slug characters', function () {
    expect(UniqueSlug::make('!!!', 'companies'))->toBe('item')
        ->and(Str::isAscii('item'))->toBeTrue();
});

test('checks every table that shares the url space', function () {
    User::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace']);
    Company::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace-2']);

    expect(UniqueSlug::make('Ada Lovelace', ['users', 'companies']))->toBe('ada-lovelace-3');
});
