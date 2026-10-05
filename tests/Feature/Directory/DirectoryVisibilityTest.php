<?php

use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view listed people and published companies', function () {
    User::factory()->listedInDirectory()->create([
        'name' => 'Ada Lovelace',
        'slug' => 'ada-lovelace',
        'photo_path' => null,
    ]);
    Company::factory()->published()->create(['name' => 'Analytical Engines', 'slug' => 'analytical-engines']);

    $this->get(route('directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/index')
            ->has('listings', 2)
            ->where('listings.0.name', 'Ada Lovelace')
            ->where('listings.0.kind', 'person')
            ->where('listings.0.photo_url', asset('images/profile-placeholder.svg'))
            ->where('listings.1.name', 'Analytical Engines')
            ->where('listings.1.kind', 'company'));

    $this->get(route('directory.show', 'ada-lovelace'))
        ->assertOk()
        ->assertSee('Ada Lovelace');

    $this->get(route('directory.show', 'analytical-engines'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('listing.kind', 'company')
            ->where('is_owner', false)
            ->where('is_published', true));
});

test('an unknown slug is not found', function () {
    $this->get(route('directory.show', 'nobody'))->assertNotFound();
});

test('guests cannot view hidden or pending people or draft companies', function () {
    $hidden = User::factory()->withCompleteProfile()->create(['name' => 'Hidden Ada']);
    $pending = User::factory()->pendingInDirectory()->create(['name' => 'Pending Ada']);
    $draft = Company::factory()->create(['name' => 'Hidden Co']);

    $this->get(route('directory.show', (string) $hidden->slug))->assertForbidden();
    $this->get(route('directory.show', (string) $pending->slug))->assertForbidden();
    $this->get(route('directory.show', $draft->slug))->assertForbidden();
    $this->get(route('directory.index'))
        ->assertDontSee('Hidden Ada')
        ->assertDontSee('Pending Ada')
        ->assertDontSee('Hidden Co');
});

test('other members cannot view a pending profile', function () {
    $pending = User::factory()->pendingInDirectory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('directory.show', (string) $pending->slug))
        ->assertForbidden();
});

test('staff can view a pending person', function () {
    $pending = User::factory()->pendingInDirectory()->create(['name' => 'Pending Ada']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('directory.show', (string) $pending->slug))
        ->assertOk()
        ->assertSee('Pending Ada');
});

test('staff can view a draft company', function () {
    $draft = Company::factory()->create(['name' => 'Draft Studio']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('directory.show', $draft->slug))
        ->assertOk()
        ->assertSee('Draft Studio');
});

test('listed profiles expose profile links', function () {
    $user = User::factory()->listedInDirectory()->create([
        'name' => 'Ada Lovelace',
        'website' => 'https://ada.dev',
        'github' => 'ada',
        'linkedin' => 'https://www.linkedin.com/in/ada',
        'x' => '@ada',
    ]);

    $this->get(route('directory.show', (string) $user->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/show')
            ->where('listing.links', [
                ['key' => 'website', 'url' => 'https://ada.dev'],
                ['key' => 'github', 'url' => 'https://github.com/ada'],
                ['key' => 'linkedin', 'url' => 'https://www.linkedin.com/in/ada'],
                ['key' => 'x', 'url' => 'https://x.com/ada'],
            ]));
});

test('the directory can be filtered to companies', function () {
    User::factory()->listedInDirectory()->create(['name' => 'Ada Lovelace']);
    Company::factory()->published()->create(['name' => 'Analytical Engine']);

    $this->get(route('directory.index', ['kind' => 'company']))
        ->assertOk()
        ->assertSee('Analytical Engine')
        ->assertDontSee('Ada Lovelace');
});

test('the directory can be filtered to people', function () {
    User::factory()->listedInDirectory()->create(['name' => 'Ada Lovelace']);
    Company::factory()->published()->create(['name' => 'Analytical Engine']);

    $this->get(route('directory.index', ['kind' => 'person']))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Analytical Engine');
});

test('people and companies are ordered alphabetically by name together', function () {
    User::factory()->listedInDirectory()->create(['name' => 'Zarif Ahmed']);
    Company::factory()->published()->create(['name' => 'brain Station']);
    User::factory()->listedInDirectory()->create(['name' => 'Ayesha Khan']);
    User::factory()->listedInDirectory()->create(['name' => 'Mahin Rahman']);

    $this->get(route('directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/index')
            ->where('listings.0.name', 'Ayesha Khan')
            ->where('listings.1.name', 'brain Station')
            ->where('listings.2.name', 'Mahin Rahman')
            ->where('listings.3.name', 'Zarif Ahmed'));
});
