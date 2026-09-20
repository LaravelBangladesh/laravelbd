<?php

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage the directory', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.directory.index'))
        ->assertForbidden();
});

test('staff can create a listing', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.directory.store'), [
            'name' => 'Ada Lovelace',
            'kind' => 'person',
            'status' => 'published',
            'title' => 'Mathematician',
            'city' => 'Dhaka',
            'github' => 'ada',
            'linkedin' => 'https://www.linkedin.com/in/ada',
            'x' => 'ada',
        ])
        ->assertRedirect(route('admin.directory.index'));

    $listing = DirectoryListing::query()->where('name', 'Ada Lovelace')->first();

    expect($listing)->not->toBeNull()
        ->and($listing?->slug)->toBe('ada-lovelace')
        ->and($listing?->kind)->toBe(DirectoryKind::Person)
        ->and($listing?->status)->toBe(DirectoryStatus::Published)
        ->and($listing?->github)->toBe('ada')
        ->and($listing?->linkedin)->toBe('https://www.linkedin.com/in/ada')
        ->and($listing?->x)->toBe('ada');
});

test('staff can update and delete a listing', function () {
    $listing = DirectoryListing::factory()->create(['name' => 'Old Name']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.directory.edit', $listing))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/directory/edit')
            ->where('listing.id', $listing->id));

    $this->actingAs($moderator)
        ->patch(route('admin.directory.update', $listing), [
            'name' => 'Analytical Engine',
            'kind' => 'company',
            'status' => 'published',
        ])
        ->assertRedirect(route('admin.directory.index'));

    expect($listing->fresh()?->name)->toBe('Analytical Engine')
        ->and($listing->fresh()?->kind)->toBe(DirectoryKind::Company);

    $this->actingAs($moderator)
        ->delete(route('admin.directory.destroy', $listing))
        ->assertRedirect(route('admin.directory.index'));

    $this->assertDatabaseMissing('directory_listings', ['id' => $listing->id]);
});
