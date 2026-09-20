<?php

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage resources', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.resources.index'))
        ->assertForbidden();
});

test('staff can create a video', function () {
    $event = Event::factory()->create();
    $speaker = Speaker::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.resources.store'), [
            'title_en' => 'Opening talk',
            'kind' => 'video',
            'status' => 'published',
            'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'event_id' => $event->id,
            'speaker_id' => $speaker->id,
        ])
        ->assertRedirect(route('admin.resources.index'));

    $resource = Resource::query()->where('title_en', 'Opening talk')->first();

    expect($resource)->not->toBeNull()
        ->and($resource?->slug)->toBe('opening-talk')
        ->and($resource?->kind)->toBe(ResourceKind::Video)
        ->and($resource?->status)->toBe(ResourceStatus::Published)
        ->and($resource?->event_id)->toBe($event->id);
});

test('staff cannot save a video without a youtube url', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.resources.store'), [
            'title_en' => 'Opening talk',
            'kind' => 'video',
            'status' => 'draft',
            'embed_url' => 'https://example.com/watch',
        ])
        ->assertSessionHasErrors('embed_url');
});

test('staff can update and delete a resource', function () {
    $resource = Resource::factory()->create(['title_en' => 'Old notes']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.resources.edit', $resource))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/resources/edit')
            ->where('resource.id', $resource->id));

    $this->actingAs($moderator)
        ->patch(route('admin.resources.update', $resource), [
            'title_en' => 'Workshop notes',
            'kind' => 'link',
            'status' => 'published',
            'url' => 'https://laravel.com/docs',
        ])
        ->assertRedirect(route('admin.resources.index'));

    expect($resource->fresh()?->title_en)->toBe('Workshop notes')
        ->and($resource->fresh()?->kind)->toBe(ResourceKind::Link);

    $this->actingAs($moderator)
        ->delete(route('admin.resources.destroy', $resource))
        ->assertRedirect(route('admin.resources.index'));

    $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
});
