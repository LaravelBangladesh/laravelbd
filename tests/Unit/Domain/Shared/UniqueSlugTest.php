<?php

use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\UniqueSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('slugs a title and suffixes collisions', function () {
    Speaker::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace']);

    expect(UniqueSlug::make('Ada Lovelace', 'speakers'))->toBe('ada-lovelace-2');
});

test('ignores the current record when renaming', function () {
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace']);

    expect(UniqueSlug::make('Ada Lovelace', 'speakers', $speaker->id))->toBe('ada-lovelace');
});

test('falls back when a title has no slug characters', function () {
    expect(UniqueSlug::make('!!!', 'speakers'))->toBe('item')
        ->and(Str::isAscii('item'))->toBeTrue();
});
