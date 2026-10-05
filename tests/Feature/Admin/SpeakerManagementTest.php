<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage speakers', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.speakers.index'))
        ->assertForbidden();
});

test('staff see derived speakers with their event count', function () {
    $moderator = User::factory()->moderator()->create();
    $host = User::factory()->withCompleteProfile()->create(['name' => 'Grace Hopper', 'title' => 'Rear Admiral', 'company' => 'Navy']);
    $first = Event::factory()->create();
    $second = Event::factory()->create();
    $first->speakers()->attach($host, ['role' => 'host']);
    $second->speakers()->attach($host, ['role' => 'speaker']);
    $accepted = User::factory()->create(['name' => 'Ada Lovelace']);
    TalkProposal::factory()->accepted()->create(['user_id' => $accepted->id]);
    User::factory()->create(['name' => 'Not A Speaker']);

    $this->actingAs($moderator)
        ->get(route('admin.speakers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/speakers/index')
            ->has('speakers', 2)
            ->where('speakers.0.name', 'Ada Lovelace')
            ->where('speakers.0.events_count', 0)
            ->where('speakers.1.id', $host->id)
            ->where('speakers.1.title', 'Rear Admiral')
            ->where('speakers.1.company', 'Navy')
            ->where('speakers.1.photo_url', $host->photoUrl())
            ->where('speakers.1.events_count', 2));
});

test('staff can add a guest speaker as a hidden, unverified member', function () {
    Storage::fake('public');
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.speakers.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/speakers/create'));

    $response = $this->actingAs($moderator)
        ->post(route('admin.speakers.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'Ada@Example.com',
            'title' => 'Mathematician',
            'company' => 'Analytical Engine',
            'photo' => UploadedFile::fake()->image('ada.jpg'),
        ]);

    $guest = User::query()->where('email', 'ada@example.com')->first();

    $response->assertRedirect(route('admin.users.edit', $guest))
        ->assertInertiaFlash('toast.message', __('admin.speaker_guest_created'));

    expect($guest?->name)->toBe('Ada Lovelace')
        ->and($guest?->title)->toBe('Mathematician')
        ->and($guest?->company)->toBe('Analytical Engine')
        ->and($guest?->photo_path)->not->toBeNull()
        ->and($guest?->email_verified_at)->toBeNull()
        ->and($guest?->directory_status)->toBe(DirectoryVisibility::Hidden)
        ->and($guest?->isStaff())->toBeFalse();
});

test('adding a guest with a known email opens the existing user instead', function () {
    $moderator = User::factory()->moderator()->create();
    $existing = User::factory()->create(['email' => 'ada@example.com', 'name' => 'Ada Lovelace']);

    $this->actingAs($moderator)
        ->post(route('admin.speakers.store'), [
            'name' => 'Someone Else',
            'email' => 'ada@example.com',
        ])
        ->assertRedirect(route('admin.users.edit', $existing))
        ->assertInertiaFlash('toast.message', __('admin.speaker_guest_exists'));

    expect(User::query()->where('email', 'ada@example.com')->count())->toBe(1)
        ->and($existing->fresh()?->name)->toBe('Ada Lovelace');
});

test('a guest speaker needs a name and a valid email', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.speakers.store'), ['name' => '', 'email' => 'not-an-email'])
        ->assertSessionHasErrors(['name', 'email']);
});

test('speakers are no longer edited or deleted here', function () {
    expect(Route::has('admin.speakers.edit'))->toBeFalse()
        ->and(Route::has('admin.speakers.update'))->toBeFalse()
        ->and(Route::has('admin.speakers.destroy'))->toBeFalse();
});
