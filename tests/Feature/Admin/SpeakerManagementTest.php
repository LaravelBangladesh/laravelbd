<?php

use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage speakers', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.speakers.index'))
        ->assertForbidden();
});

test('staff can create a speaker', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.speakers.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/speakers/create'));

    $this->actingAs($moderator)
        ->post(route('admin.speakers.store'), [
            'name' => 'Ada Lovelace',
            'title' => 'Mathematician',
            'company' => 'Analytical Engine',
        ])
        ->assertRedirect(route('admin.speakers.index'));

    $speaker = Speaker::query()->where('name', 'Ada Lovelace')->first();

    expect($speaker)->not->toBeNull()
        ->and($speaker?->slug)->toBe('ada-lovelace')
        ->and($speaker?->title)->toBe('Mathematician');
});

test('staff can update and delete a speaker', function () {
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.speakers.edit', $speaker))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/speakers/edit')
            ->where('speaker.id', $speaker->id));

    $this->actingAs($moderator)
        ->patch(route('admin.speakers.update', $speaker), [
            'name' => 'Augusta Ada King',
        ])
        ->assertRedirect(route('admin.speakers.index'));

    expect($speaker->fresh()?->name)->toBe('Augusta Ada King')
        ->and($speaker->fresh()?->slug)->toBe('augusta-ada-king');

    $this->actingAs($moderator)
        ->delete(route('admin.speakers.destroy', $speaker))
        ->assertRedirect(route('admin.speakers.index'));

    $this->assertDatabaseMissing('speakers', ['id' => $speaker->id]);
});

test('staff can list speakers', function () {
    Speaker::factory()->create(['name' => 'Grace Hopper']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.speakers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/speakers/index')
            ->has('speakers', 1)
            ->where('speakers.0.name', 'Grace Hopper'));
});
