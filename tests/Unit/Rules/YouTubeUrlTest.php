<?php

use App\Domain\Shared\Rules\YouTubeUrl;
use Illuminate\Support\Facades\Validator;

test('accepts a youtube watch url', function () {
    $validator = Validator::make(
        ['embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ['embed_url' => [new YouTubeUrl]],
    );

    expect($validator->passes())->toBeTrue();
});

test('rejects a random url', function () {
    $validator = Validator::make(
        ['embed_url' => 'https://example.com/watch'],
        ['embed_url' => [new YouTubeUrl]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('embed_url'))->toBe(__('events.media.youtube_invalid'));
});
