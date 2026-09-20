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
        'speaker_title' => 'Rear Admiral',
        'speaker_company' => 'Navy',
    ]);

    expect($withRole->source)->toBe('existing')
        ->and($withRole->speakerId)->toBe('abc')
        ->and($withRole->role)->toBe('moderator')
        ->and($withoutRole->role)->toBe(SpeakerRole::Speaker->value)
        ->and($withoutRole->name)->toBe('Grace Hopper')
        ->and($withoutRole->title)->toBe('Rear Admiral')
        ->and($withoutRole->company)->toBe('Navy')
        ->and(SessionSpeakerData::fromValidated([])->source)->toBeNull();
});
