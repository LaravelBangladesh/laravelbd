<?php

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('published returns only published listings', function () {
    $published = DirectoryListing::factory()->create(['status' => DirectoryStatus::Published]);
    $draft = DirectoryListing::factory()->create(['status' => DirectoryStatus::Draft]);

    expect(DirectoryListing::query()->published()->pluck('id')->all())
        ->toContain($published->id)
        ->not->toContain($draft->id);
});

test('draft returns only draft listings', function () {
    $draft = DirectoryListing::factory()->create(['status' => DirectoryStatus::Draft]);
    $published = DirectoryListing::factory()->create(['status' => DirectoryStatus::Published]);

    expect(DirectoryListing::query()->draft()->pluck('id')->all())
        ->toContain($draft->id)
        ->not->toContain($published->id);
});

test('of kind filters by kind', function () {
    $person = DirectoryListing::factory()->create(['kind' => DirectoryKind::Person]);
    $company = DirectoryListing::factory()->create(['kind' => DirectoryKind::Company]);

    expect(DirectoryListing::query()->ofKind(DirectoryKind::Person)->pluck('id')->all())
        ->toContain($person->id)
        ->not->toContain($company->id);
});

test('of kind without a kind keeps every listing', function () {
    DirectoryListing::factory()->count(2)->create();

    expect(DirectoryListing::query()->ofKind(null)->get())->toHaveCount(2);
});

test('alphabetical orders listings by name ascending', function () {
    DirectoryListing::factory()->create(['name' => 'Zara']);
    DirectoryListing::factory()->create(['name' => 'Anik']);

    expect(DirectoryListing::query()->alphabetical()->pluck('name')->all())
        ->toBe(['Anik', 'Zara']);
});
