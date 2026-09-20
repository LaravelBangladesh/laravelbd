<?php

use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view published listings', function () {
    $listing = DirectoryListing::factory()->published()->create([
        'name' => 'Ada Lovelace',
    ]);

    $this->get(route('directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/index')
            ->has('listings', 1)
            ->where('listings.0.name', 'Ada Lovelace')
            ->where('listings.0.photo_url', asset('images/profile-placeholder.svg')));

    $this->get(route('directory.show', $listing))
        ->assertOk()
        ->assertSee('Ada Lovelace');
});

test('guests cannot view draft listings', function () {
    $listing = DirectoryListing::factory()->create(['name' => 'Hidden Co']);

    $this->get(route('directory.show', $listing))->assertForbidden();
    $this->get(route('directory.index'))->assertDontSee('Hidden Co');
});

test('staff can view draft listings', function () {
    $listing = DirectoryListing::factory()->create(['name' => 'Draft Studio']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('directory.show', $listing))
        ->assertOk()
        ->assertSee('Draft Studio');
});

test('published listings expose profile links', function () {
    $listing = DirectoryListing::factory()->published()->create([
        'name' => 'Ada Lovelace',
        'website' => 'https://ada.dev',
        'github' => 'ada',
        'linkedin' => 'https://www.linkedin.com/in/ada',
        'x' => '@ada',
    ]);

    $this->get(route('directory.show', $listing))
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

test('published listings can be filtered by kind', function () {
    DirectoryListing::factory()->published()->create(['name' => 'Ada Lovelace']);
    DirectoryListing::factory()->published()->company()->create(['name' => 'Analytical Engine']);

    $this->get(route('directory.index', ['kind' => 'company']))
        ->assertOk()
        ->assertSee('Analytical Engine')
        ->assertDontSee('Ada Lovelace');
});

test('published listings are ordered alphabetically by name', function () {
    DirectoryListing::factory()->published()->create(['name' => 'Zarif Ahmed']);
    DirectoryListing::factory()->published()->create(['name' => 'Ayesha Khan']);
    DirectoryListing::factory()->published()->create(['name' => 'Mahin Rahman']);

    $this->get(route('directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/index')
            ->where('listings.0.name', 'Ayesha Khan')
            ->where('listings.1.name', 'Mahin Rahman')
            ->where('listings.2.name', 'Zarif Ahmed'));
});
