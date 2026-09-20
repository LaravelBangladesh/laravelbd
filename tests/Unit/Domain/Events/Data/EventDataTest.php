<?php

use App\Domain\Events\Data\EventData;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;

test('builds event data from validated input', function () {
    $data = EventData::fromValidated([
        'title_en' => 'October Meetup',
        'title_bn' => 'অক্টোবর',
        'excerpt_en' => 'Short',
        'excerpt_bn' => null,
        'description_en' => 'Long',
        'description_bn' => null,
        'type' => EventType::Workshop->value,
        'status' => EventStatus::Published->value,
        'venue_name' => 'Dhaka Hall',
        'venue_address' => 'Gulshan',
        'venue_map_url' => 'https://maps.example/1',
        'online_url' => null,
        'starts_at' => '2030-10-01T10:00',
        'ends_at' => '2030-10-01T12:00',
        'capacity' => '120',
    ]);

    expect($data->titleEn)->toBe('October Meetup')
        ->and($data->titleBn)->toBe('অক্টোবর')
        ->and($data->excerptBn)->toBeNull()
        ->and($data->type)->toBe(EventType::Workshop)
        ->and($data->status)->toBe(EventStatus::Published)
        ->and($data->venueName)->toBe('Dhaka Hall')
        ->and($data->onlineUrl)->toBeNull()
        ->and($data->capacity)->toBe(120)
        ->and($data->startsAt->toIso8601String())->toBe('2030-10-01T04:00:00+00:00')
        ->and($data->attributes()['published_at'])->not->toBeNull();
});

test('omits optional fields and a published date for drafts', function () {
    $data = EventData::fromValidated([
        'title_en' => 'Draft',
        'type' => EventType::Meetup->value,
        'status' => EventStatus::Draft->value,
        'starts_at' => '2030-10-01T10:00',
        'ends_at' => '2030-10-01T12:00',
        'capacity' => null,
    ]);

    expect($data->capacity)->toBeNull()
        ->and($data->titleBn)->toBeNull()
        ->and($data->venueName)->toBeNull()
        ->and($data->attributes()['published_at'])->toBeNull();
});
