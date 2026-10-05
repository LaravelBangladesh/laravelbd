<?php

use App\Domain\Directory\Actions\CreateCompany;
use App\Domain\Directory\Actions\DeleteCompany;
use App\Domain\Directory\Actions\UpdateCompany;
use App\Domain\Directory\Data\CompanyData;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function companyData(array $overrides = []): CompanyData
{
    return CompanyData::fromValidated([
        'name' => 'Analytical Engines',
        ...$overrides,
    ]);
}

test('create company stores a slugged draft', function () {
    $user = User::factory()->create();

    $company = app(CreateCompany::class)(companyData(), null, $user->id);

    expect($company->slug)->toBe('analytical-engines')
        ->and($company->created_by)->toBe($user->id)
        ->and($company->status)->toBe(DirectoryStatus::Draft)
        ->and($company->photo_path)->toBeNull();
});

test('create company stores a logo and publishes', function () {
    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('put')->once()->with('bytes', 'logo.png', 'directory')->andReturn('directory/logo.png');
    app()->instance(ImageStorage::class, $images);

    $company = app(CreateCompany::class)(
        companyData(['status' => DirectoryStatus::Published->value]),
        new UploadedImage('bytes', 'logo.png'),
        null,
    );

    expect($company->photo_path)->toBe('directory/logo.png')
        ->and($company->published_at)->not->toBeNull();
});

test('company slugs never collide with a person in the directory', function () {
    User::factory()->create(['slug' => 'analytical-engines']);

    $company = app(CreateCompany::class)(companyData(), null, null);

    expect($company->slug)->toBe('analytical-engines-2');
});

test('update company rewrites attributes and keeps the published date', function () {
    $published = now()->subWeek();
    $company = Company::factory()->create([
        'status' => DirectoryStatus::Published,
        'published_at' => $published,
    ]);

    $updated = app(UpdateCompany::class)(
        $company,
        companyData(['name' => 'Renamed Co', 'status' => DirectoryStatus::Published->value]),
        null,
    );

    expect($updated->name)->toBe('Renamed Co')
        ->and($updated->slug)->toBe('renamed-co')
        ->and($updated->published_at?->toDateTimeString())->toBe($published->toDateTimeString());
});

test('update company replaces an existing logo', function () {
    $company = Company::factory()->create(['photo_path' => 'directory/old.png']);

    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('directory/old.png');
    $images->shouldReceive('put')->once()->andReturn('directory/new.png');
    app()->instance(ImageStorage::class, $images);

    $updated = app(UpdateCompany::class)($company, companyData(), new UploadedImage('bytes', 'new.png'));

    expect($updated->photo_path)->toBe('directory/new.png');
});

test('delete company removes the logo and the row', function () {
    $company = Company::factory()->create(['photo_path' => 'directory/old.png']);

    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('directory/old.png');
    app()->instance(ImageStorage::class, $images);

    app(DeleteCompany::class)($company);

    expect(Company::query()->whereKey($company->id)->exists())->toBeFalse();
});
