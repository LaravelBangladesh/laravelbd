<?php

use App\Domain\Events\Data\SessionData;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;

test('builds session data from validated input', function () {
    $data = SessionData::fromValidated([
        'title_en' => 'Keynote',
        'title_bn' => 'মূল বক্তব্য',
        'description_en' => 'Opening',
        'kind' => SessionKind::Keynote->value,
        'starts_at' => '2030-10-01T10:00',
        'ends_at' => '2030-10-01T11:00',
        'room' => 'Hall A',
        'recording_url' => 'https://youtu.be/abc',
        'sort_order' => '3',
        'speaker_source' => 'new',
        'speaker_name' => 'Grace Hopper',
        'speaker_role' => SpeakerRole::Host->value,
    ]);

    expect($data->titleEn)->toBe('Keynote')
        ->and($data->kind)->toBe(SessionKind::Keynote)
        ->and($data->room)->toBe('Hall A')
        ->and($data->recordingUrl)->toBe('https://youtu.be/abc')
        ->and($data->sortOrder)->toBe(3)
        ->and($data->speaker->name)->toBe('Grace Hopper')
        ->and($data->attributes(9)['sort_order'])->toBe(3);
});

test('falls back to the given sort order when none is supplied', function () {
    $data = SessionData::fromValidated([
        'title_en' => 'Break',
        'kind' => SessionKind::Break->value,
        'starts_at' => '2030-10-01T10:00',
        'ends_at' => '2030-10-01T11:00',
        'sort_order' => '',
    ]);

    expect($data->sortOrder)->toBeNull()
        ->and($data->descriptionEn)->toBeNull()
        ->and($data->attributes(9)['sort_order'])->toBe(9);
});
