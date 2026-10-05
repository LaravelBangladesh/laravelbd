<?php

use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Enums\SpeakerRole;

test('builds speaker assignment data and defaults the role', function () {
    $withRole = SessionSpeakerData::fromValidated([
        'speaker_source' => 'existing',
        'speaker_id' => 'abc',
        'speaker_role' => SpeakerRole::Moderator->value,
    ]);

    $withoutRole = SessionSpeakerData::fromValidated([
        'speaker_source' => 'new',
        'speaker_name' => 'Grace Hopper',
        'speaker_email' => 'grace@example.com',
        'speaker_title' => 'Rear Admiral',
        'speaker_company' => 'Navy',
    ]);

    expect($withRole->source)->toBe('existing')
        ->and($withRole->speakerId)->toBe('abc')
        ->and($withRole->role)->toBe('moderator')
        ->and($withoutRole->role)->toBe(SpeakerRole::Speaker->value)
        ->and($withoutRole->name)->toBe('Grace Hopper')
        ->and($withoutRole->email)->toBe('grace@example.com')
        ->and($withoutRole->title)->toBe('Rear Admiral')
        ->and($withoutRole->company)->toBe('Navy')
        ->and(SessionSpeakerData::fromValidated([])->source)->toBeNull();
});

test('names a guest only for a new speaker with a name and email', function () {
    $guest = SessionSpeakerData::fromValidated([
        'speaker_source' => 'new',
        'speaker_name' => 'Grace Hopper',
        'speaker_email' => 'grace@example.com',
        'speaker_title' => '',
    ])->guest();

    expect($guest?->name)->toBe('Grace Hopper')
        ->and($guest?->email)->toBe('grace@example.com')
        ->and($guest?->title)->toBeNull()
        ->and($guest?->company)->toBeNull()
        ->and(SessionSpeakerData::fromValidated(['speaker_source' => 'new', 'speaker_name' => 'Grace'])->guest())->toBeNull()
        ->and(SessionSpeakerData::fromValidated(['speaker_source' => 'new', 'speaker_email' => 'g@example.com'])->guest())->toBeNull()
        ->and(SessionSpeakerData::fromValidated(['speaker_source' => 'existing', 'speaker_name' => 'Grace', 'speaker_email' => 'g@example.com'])->guest())->toBeNull();
});
