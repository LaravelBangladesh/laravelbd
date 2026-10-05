<?php

use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a company links the staff member who created it', function () {
    $author = User::factory()->moderator()->create();

    $company = Company::factory()->create(['created_by' => $author->id]);

    expect($company->creator?->id)->toBe($author->id);
});

test('a company shows its logo or nothing', function () {
    Storage::fake('public');

    expect(Company::factory()->make(['photo_path' => null])->photoUrl())->toBeNull()
        ->and(Company::factory()->make(['photo_path' => 'directory/acme.png'])->photoUrl())
        ->toBe(Storage::disk('public')->url('directory/acme.png'));
});
