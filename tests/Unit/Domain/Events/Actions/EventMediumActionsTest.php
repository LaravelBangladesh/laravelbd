<?php

use App\Domain\Events\Actions\DeleteEventMedium;
use App\Domain\Events\Actions\StoreEventMedium;
use App\Domain\Events\Data\EventMediumData;
use App\Domain\Events\Enums\MediaKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('stores a photo medium with an incrementing sort order', function () {
    $event = Event::factory()->create();

    $first = app(StoreEventMedium::class)(
        $event,
        EventMediumData::fromValidated(['kind' => MediaKind::Photo->value, 'caption_en' => 'Hall']),
        new UploadedImage('binary', 'hall.jpg'),
    );
    $second = app(StoreEventMedium::class)(
        $event,
        EventMediumData::fromValidated(['kind' => MediaKind::Photo->value]),
        new UploadedImage('binary', 'crowd.jpg'),
    );

    expect($first->sort_order)->toBe(0)
        ->and($first->path)->not->toBeNull()
        ->and($first->caption_en)->toBe('Hall')
        ->and($first->embed_url)->toBeNull()
        ->and($second->sort_order)->toBe(1);
});

test('stores a video medium without a path', function () {
    $event = Event::factory()->create();

    $medium = app(StoreEventMedium::class)($event, EventMediumData::fromValidated([
        'kind' => MediaKind::Video->value,
        'embed_url' => 'https://www.youtube.com/watch?v=abc',
    ]));

    expect($medium->path)->toBeNull()
        ->and($medium->embed_url)->toBe('https://www.youtube.com/watch?v=abc');
});

test('deletes a medium and its file', function () {
    $medium = Event::factory()->create()->media()->create([
        'kind' => MediaKind::Photo,
        'path' => 'events/photo.jpg',
        'sort_order' => 0,
    ]);
    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('events/photo.jpg');

    (new DeleteEventMedium($images))($medium);

    expect(EventMedium::query()->whereKey($medium->id)->exists())->toBeFalse();
});
