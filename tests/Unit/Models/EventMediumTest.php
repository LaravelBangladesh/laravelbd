<?php

use App\Domain\Events\Enums\MediaKind;
use App\Domain\Events\Models\EventMedium;

test('photos are recognised and videos resolve an embed', function () {
    $photo = new EventMedium([
        'kind' => MediaKind::Photo,
        'path' => 'events/hall.jpg',
    ]);
    $video = new EventMedium([
        'kind' => MediaKind::Video,
        'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);
    $empty = new EventMedium([
        'kind' => MediaKind::Photo,
        'path' => null,
    ]);

    expect($photo->isPhoto())->toBeTrue()
        ->and($photo->embedSrc())->toBeNull()
        ->and($video->isPhoto())->toBeFalse()
        ->and($video->embedSrc())->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
        ->and($empty->isPhoto())->toBeFalse();
});
