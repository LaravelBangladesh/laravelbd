<?php

use App\Application\Content\ViewModels\ResourceJsonLd;
use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an article resource is published as an article', function () {
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace']);

    $resource = Resource::factory()->published()->create([
        'slug' => 'queues-in-depth',
        'title_en' => 'Queues in depth',
        'excerpt_en' => 'How Laravel queues work.',
        'speaker_id' => $speaker->id,
        'published_at' => '2026-02-01 10:00:00',
    ]);

    $schema = ResourceJsonLd::make($resource->load('speaker'));

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('Article')
        ->and($schema['headline'])->toBe('Queues in depth')
        ->and($schema['description'])->toBe('How Laravel queues work.')
        ->and($schema['url'])->toBe(route('resources.show', 'queues-in-depth'))
        ->and($schema['author'])->toBe(['@type' => 'Person', 'name' => 'Ada Lovelace'])
        ->and($schema['publisher']['@type'])->toBe('Organization')
        ->and($schema['datePublished'])->toBe('2026-02-01T10:00:00+00:00');
});

test('an article without a speaker omits the author', function () {
    $resource = Resource::factory()->published()->create(['speaker_id' => null]);

    expect(ResourceJsonLd::make($resource->load('speaker')))->not->toHaveKey('author');
});

test('a link resource is published as a web page', function () {
    $resource = Resource::factory()->link()->published()->create([
        'slug' => 'workshop-notes',
        'title_en' => 'Workshop notes',
        'excerpt_en' => 'Notes from the workshop.',
    ]);

    expect(ResourceJsonLd::make($resource))->toBe([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => 'Workshop notes',
        'description' => 'Notes from the workshop.',
        'url' => route('resources.show', 'workshop-notes'),
    ]);
});

test('a video resource derives its urls from each youtube url shape', function (string $embedUrl) {
    $resource = Resource::factory()->video()->published()->create([
        'slug' => 'talk-recording',
        'title_en' => 'Talk recording',
        'excerpt_en' => 'The recorded talk.',
        'embed_url' => $embedUrl,
        'published_at' => '2026-02-01 10:00:00',
    ]);

    $schema = ResourceJsonLd::make($resource);

    expect($schema['@type'])->toBe('VideoObject')
        ->and($schema['name'])->toBe('Talk recording')
        ->and($schema['url'])->toBe(route('resources.show', 'talk-recording'))
        ->and($schema['embedUrl'])->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
        ->and($schema['contentUrl'])->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->and($schema['thumbnailUrl'])->toBe('https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg')
        ->and($schema['uploadDate'])->toBe('2026-02-01T10:00:00+00:00');
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ',
]);

test('a video without a usable embed url omits the video urls', function () {
    $resource = Resource::factory()->video()->published()->create([
        'embed_url' => null,
        'published_at' => null,
    ]);

    $schema = ResourceJsonLd::make($resource);

    expect($schema['@type'])->toBe('VideoObject')
        ->and($schema)->not->toHaveKey('embedUrl')
        ->and($schema)->not->toHaveKey('contentUrl')
        ->and($schema)->not->toHaveKey('thumbnailUrl')
        ->and($schema)->not->toHaveKey('uploadDate');
});
