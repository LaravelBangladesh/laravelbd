<?php

use App\Domain\Content\Data\ResourceData;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;

test('from validated maps every field', function () {
    $data = ResourceData::fromValidated([
        'title_en' => 'Title en',
        'title_bn' => 'Title bn',
        'excerpt_en' => 'Excerpt en',
        'excerpt_bn' => 'Excerpt bn',
        'description_en' => 'Description en',
        'description_bn' => 'Description bn',
        'kind' => ResourceKind::Video->value,
        'status' => ResourceStatus::Published->value,
        'url' => 'https://example.com',
        'embed_url' => 'https://youtu.be/abc',
        'event_id' => 'event-uuid',
        'speaker_id' => 'speaker-uuid',
    ]);

    expect($data->titleEn)->toBe('Title en')
        ->and($data->titleBn)->toBe('Title bn')
        ->and($data->excerptEn)->toBe('Excerpt en')
        ->and($data->excerptBn)->toBe('Excerpt bn')
        ->and($data->descriptionEn)->toBe('Description en')
        ->and($data->descriptionBn)->toBe('Description bn')
        ->and($data->kind)->toBe(ResourceKind::Video)
        ->and($data->status)->toBe(ResourceStatus::Published)
        ->and($data->url)->toBe('https://example.com')
        ->and($data->embedUrl)->toBe('https://youtu.be/abc')
        ->and($data->eventId)->toBe('event-uuid')
        ->and($data->speakerId)->toBe('speaker-uuid');
});

test('from validated nulls blank and missing fields', function () {
    $data = ResourceData::fromValidated([
        'title_en' => 'Title en',
        'kind' => ResourceKind::Article->value,
        'status' => ResourceStatus::Draft->value,
        'url' => '',
    ]);

    expect($data->titleBn)->toBeNull()
        ->and($data->excerptEn)->toBeNull()
        ->and($data->excerptBn)->toBeNull()
        ->and($data->descriptionEn)->toBeNull()
        ->and($data->descriptionBn)->toBeNull()
        ->and($data->url)->toBeNull()
        ->and($data->embedUrl)->toBeNull()
        ->and($data->eventId)->toBeNull()
        ->and($data->speakerId)->toBeNull();
});

test('attributes stamps published at when publishing', function () {
    $data = ResourceData::fromValidated([
        'title_en' => 'Title en',
        'kind' => ResourceKind::Article->value,
        'status' => ResourceStatus::Published->value,
    ]);

    expect($data->attributes(null)['published_at'])->not->toBeNull();
});

test('attributes keeps an existing published date', function () {
    $published = now()->subWeek();

    $data = ResourceData::fromValidated([
        'title_en' => 'Title en',
        'kind' => ResourceKind::Article->value,
        'status' => ResourceStatus::Published->value,
    ]);

    expect($data->attributes($published)['published_at'])->toBe($published);
});

test('attributes clears published at for a draft', function () {
    $data = ResourceData::fromValidated([
        'title_en' => 'Title en',
        'kind' => ResourceKind::Article->value,
        'status' => ResourceStatus::Draft->value,
    ]);

    $attributes = $data->attributes(now());

    expect($attributes['published_at'])->toBeNull()
        ->and($attributes['title_en'])->toBe('Title en')
        ->and($attributes['kind'])->toBe(ResourceKind::Article);
});
