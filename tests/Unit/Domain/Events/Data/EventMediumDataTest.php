<?php

use App\Domain\Events\Data\EventMediumData;
use App\Domain\Events\Enums\MediaKind;

test('keeps the embed url only for videos', function () {
    $video = EventMediumData::fromValidated([
        'kind' => MediaKind::Video->value,
        'embed_url' => 'https://youtu.be/abc',
        'caption_en' => 'Recording',
    ]);

    $photo = EventMediumData::fromValidated([
        'kind' => MediaKind::Photo->value,
        'embed_url' => 'https://youtu.be/abc',
    ]);

    expect($video->kind)->toBe(MediaKind::Video)
        ->and($video->embedUrl)->toBe('https://youtu.be/abc')
        ->and($video->captionEn)->toBe('Recording')
        ->and($photo->embedUrl)->toBeNull()
        ->and($photo->captionEn)->toBeNull()
        ->and($photo->captionBn)->toBeNull();
});
