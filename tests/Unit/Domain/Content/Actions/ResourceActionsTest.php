<?php

use App\Domain\Content\Actions\CreateResource;
use App\Domain\Content\Actions\DeleteResource;
use App\Domain\Content\Actions\UpdateResource;
use App\Domain\Content\Data\ResourceData;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\Models\Resource;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function resourceData(array $overrides = []): ResourceData
{
    return ResourceData::fromValidated([
        'title_en' => 'Laravel in Dhaka',
        'kind' => ResourceKind::Article->value,
        'status' => ResourceStatus::Draft->value,
        'url' => 'https://example.com',
        ...$overrides,
    ]);
}

test('create resource stores a slugged draft owned by the creator', function () {
    $user = User::factory()->create();

    $resource = (new CreateResource)(resourceData(), $user->id);

    expect($resource->slug)->toBe('laravel-in-dhaka')
        ->and($resource->created_by)->toBe($user->id)
        ->and($resource->status)->toBe(ResourceStatus::Draft)
        ->and($resource->published_at)->toBeNull()
        ->and($resource->exists)->toBeTrue();
});

test('create resource publishes with a timestamp', function () {
    $resource = (new CreateResource)(
        resourceData(['status' => ResourceStatus::Published->value]),
        null,
    );

    expect($resource->published_at)->not->toBeNull()
        ->and($resource->created_by)->toBeNull();
});

test('create resource disambiguates a duplicate slug', function () {
    Resource::factory()->create(['slug' => 'laravel-in-dhaka']);

    $resource = (new CreateResource)(resourceData(), null);

    expect($resource->slug)->toBe('laravel-in-dhaka-2');
});

test('update resource rewrites attributes and keeps the published date', function () {
    $published = now()->subWeek();
    $resource = Resource::factory()->create([
        'status' => ResourceStatus::Published,
        'published_at' => $published,
    ]);

    $updated = (new UpdateResource)($resource, resourceData([
        'title_en' => 'Renamed Talk',
        'status' => ResourceStatus::Published->value,
    ]));

    expect($updated->title_en)->toBe('Renamed Talk')
        ->and($updated->slug)->toBe('renamed-talk')
        ->and($updated->published_at?->toDateTimeString())->toBe($published->toDateTimeString());
});

test('update resource clears the published date when unpublishing', function () {
    $resource = Resource::factory()->create([
        'status' => ResourceStatus::Published,
        'published_at' => now(),
    ]);

    $updated = (new UpdateResource)($resource, resourceData());

    expect($updated->status)->toBe(ResourceStatus::Draft)
        ->and($updated->published_at)->toBeNull();
});

test('delete resource removes the row', function () {
    $resource = Resource::factory()->create();

    (new DeleteResource)($resource);

    expect(Resource::query()->whereKey($resource->id)->exists())->toBeFalse();
});
