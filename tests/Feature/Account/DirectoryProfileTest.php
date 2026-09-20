<?php

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot open the directory profile form', function () {
    $this->get(route('account.directory.edit'))->assertRedirect(route('login'));
});

test('members can create a draft person listing', function () {
    $member = User::factory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($member)
        ->get(route('account.directory.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/directory')
            ->where('listing.name', 'Ada Lovelace'));

    $this->actingAs($member)
        ->post(route('account.directory.store'), [
            'name' => 'Ada Lovelace',
            'title' => 'Mathematician',
            'city' => 'Dhaka',
            'kind' => 'company',
            'status' => 'published',
            'github' => 'ada',
        ])
        ->assertRedirect(route('account.directory.edit'));

    $listing = $member->fresh()?->directoryListing;

    expect($listing)->not->toBeNull()
        ->and($listing?->kind)->toBe(DirectoryKind::Person)
        ->and($listing?->status)->toBe(DirectoryStatus::Draft)
        ->and($listing?->user_id)->toBe($member->id)
        ->and($listing?->github)->toBe('ada');

    $this->get(route('directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('listings', []));

    $this->actingAs($member)
        ->get(route('directory.show', $listing))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/show')
            ->where('is_owner', true)
            ->where('is_published', false));
});

test('members cannot create a second listing', function () {
    $member = User::factory()->create();
    DirectoryListing::factory()->create(['user_id' => $member->id, 'name' => 'First']);

    $this->actingAs($member)
        ->post(route('account.directory.store'), [
            'name' => 'Second',
        ])
        ->assertRedirect(route('account.directory.edit'));

    expect(DirectoryListing::query()->where('user_id', $member->id)->count())->toBe(1)
        ->and($member->fresh()?->directoryListing?->name)->toBe('First');
});

test('members can update their listing without changing publish state', function () {
    $member = User::factory()->create();
    $listing = DirectoryListing::factory()->published()->create([
        'user_id' => $member->id,
        'name' => 'Old Name',
        'kind' => DirectoryKind::Person,
    ]);

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'status' => 'draft',
            'kind' => 'company',
        ])
        ->assertRedirect(route('account.directory.edit'));

    $listing->refresh();

    expect($listing->name)->toBe('Ada Lovelace')
        ->and($listing->kind)->toBe(DirectoryKind::Person)
        ->and($listing->status)->toBe(DirectoryStatus::Published);
});

test('members cannot update someone else listing from the account form', function () {
    $member = User::factory()->create();
    DirectoryListing::factory()->create(['name' => 'Other']);

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Hijacked',
        ])
        ->assertNotFound();
});

test('other members cannot view a draft listing', function () {
    $owner = User::factory()->create();
    $listing = DirectoryListing::factory()->create([
        'user_id' => $owner->id,
        'name' => 'Hidden Ada',
    ]);
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get(route('directory.show', $listing))
        ->assertForbidden();
});

test('members can attach a photo larger than the old five megabyte cap', function () {
    Storage::fake('public');

    $member = User::factory()->create(['name' => 'Ada Lovelace']);
    $photo = UploadedFile::fake()->image('portrait.png', 120, 120)->size(6000);

    $this->actingAs($member)
        ->post(route('account.directory.store'), [
            'name' => 'Ada Lovelace',
            'photo' => $photo,
        ])
        ->assertRedirect(route('account.directory.edit'));

    $listing = $member->fresh()?->directoryListing;

    expect($listing?->photo_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists((string) $listing?->photo_path))->toBeTrue();
});

test('the account page links to the directory profile', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/edit')
            ->where('directory', null));

    $listing = DirectoryListing::factory()->create([
        'user_id' => $member->id,
        'slug' => 'ada-lovelace',
        'name' => 'Ada Lovelace',
    ]);

    expect($listing->user_id)->toBe($member->id);

    $member->unsetRelation('directoryListing');

    $this->actingAs($member)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('directory.slug', 'ada-lovelace')
            ->where('directory.is_published', false));
});
