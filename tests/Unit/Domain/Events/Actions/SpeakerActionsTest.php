<?php

use App\Domain\Events\Actions\AttachEventSpeaker;
use App\Domain\Events\Actions\CreateSpeaker;
use App\Domain\Events\Actions\DeleteSpeaker;
use App\Domain\Events\Actions\DetachEventSpeaker;
use App\Domain\Events\Actions\UpdateSpeaker;
use App\Domain\Events\Data\SpeakerData;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates a speaker with a slug and photo', function () {
    $speaker = app(CreateSpeaker::class)(
        SpeakerData::fromValidated(['name' => 'Grace Hopper', 'title' => 'Rear Admiral']),
        new UploadedImage('binary', 'grace.jpg'),
    );

    expect($speaker->exists)->toBeTrue()
        ->and($speaker->slug)->toBe('grace-hopper')
        ->and($speaker->title)->toBe('Rear Admiral')
        ->and($speaker->photo_path)->not->toBeNull();
});

test('creates a speaker without a photo', function () {
    $speaker = app(CreateSpeaker::class)(SpeakerData::fromValidated(['name' => 'Ada Lovelace']));

    expect($speaker->photo_path)->toBeNull();
});

test('updates a speaker and replaces the photo', function () {
    $speaker = Speaker::factory()->create(['photo_path' => 'speakers/old.jpg']);

    app(UpdateSpeaker::class)(
        $speaker,
        SpeakerData::fromValidated(['name' => 'Alan Turing']),
        new UploadedImage('binary', 'alan.jpg'),
    );

    expect($speaker->fresh()?->name)->toBe('Alan Turing')
        ->and($speaker->fresh()?->slug)->toBe('alan-turing')
        ->and($speaker->fresh()?->photo_path)->not->toBe('speakers/old.jpg');
});

test('updates a speaker keeping the existing photo', function () {
    $speaker = Speaker::factory()->create(['photo_path' => 'speakers/old.jpg']);

    app(UpdateSpeaker::class)($speaker, SpeakerData::fromValidated(['name' => $speaker->name]));

    expect($speaker->fresh()?->photo_path)->toBe('speakers/old.jpg');
});

test('deletes a speaker and its photo', function () {
    $speaker = Speaker::factory()->create(['photo_path' => 'speakers/grace.jpg']);
    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('speakers/grace.jpg');

    (new DeleteSpeaker($images))($speaker);

    expect(Speaker::query()->whereKey($speaker->id)->exists())->toBeFalse();
});

test('attaches and detaches a speaker on the event roster', function () {
    $event = Event::factory()->create();
    $speaker = Speaker::factory()->create();

    app(AttachEventSpeaker::class)($event, $speaker->id, SpeakerRole::Host->value);

    expect($event->speakers()->first()?->pivot->role)->toBe('host');

    app(DetachEventSpeaker::class)($event, $speaker);

    expect($event->speakers()->whereKey($speaker->id)->exists())->toBeFalse();
});
