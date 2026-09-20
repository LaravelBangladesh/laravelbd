<?php

use App\Domain\Shared\VideoEmbed;

test('extracts a youtube video id from common urls', function (string $url) {
    expect(VideoEmbed::id($url))->toBe('dQw4w9WgXcQ')
        ->and(VideoEmbed::src($url))->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ',
]);

test('rejects a non-youtube url', function () {
    expect(VideoEmbed::id('https://example.com/watch?v=dQw4w9WgXcQ'))->toBeNull()
        ->and(VideoEmbed::src('https://example.com/watch'))->toBeNull();
});
