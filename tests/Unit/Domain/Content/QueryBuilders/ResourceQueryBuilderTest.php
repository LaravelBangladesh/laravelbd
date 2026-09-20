<?php

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('published returns only published resources', function () {
    $published = Resource::factory()->create(['status' => ResourceStatus::Published]);
    $draft = Resource::factory()->create(['status' => ResourceStatus::Draft]);

    expect(Resource::query()->published()->pluck('id')->all())
        ->toContain($published->id)
        ->not->toContain($draft->id);
});

test('of kind filters by kind', function () {
    $article = Resource::factory()->create(['kind' => ResourceKind::Article]);
    $video = Resource::factory()->create([
        'kind' => ResourceKind::Video,
        'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    expect(Resource::query()->ofKind(ResourceKind::Article)->pluck('id')->all())
        ->toContain($article->id)
        ->not->toContain($video->id);
});

test('of kind without a kind keeps every resource', function () {
    Resource::factory()->count(2)->create();

    expect(Resource::query()->ofKind(null)->get())->toHaveCount(2);
});

test('newest first orders by published date then creation', function () {
    $older = Resource::factory()->create(['published_at' => now()->subWeek()]);
    $newer = Resource::factory()->create(['published_at' => now()]);

    expect(Resource::query()->newestFirst()->pluck('id')->all())
        ->toBe([$newer->id, $older->id]);
});
