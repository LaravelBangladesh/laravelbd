<?php

use App\Domain\Directory\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('published and draft split companies by status', function () {
    $published = Company::factory()->published()->create();
    $draft = Company::factory()->create();

    expect(Company::query()->published()->pluck('id')->all())->toBe([$published->id])
        ->and(Company::query()->draft()->pluck('id')->all())->toBe([$draft->id]);
});
