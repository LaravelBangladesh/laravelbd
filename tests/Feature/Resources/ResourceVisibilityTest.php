<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view published resources', function () {
    $resource = Resource::factory()->published()->create([
        'title_en' => 'Laravel docs',
    ]);

    $this->get(route('resources.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('resources/index')
            ->has('resources', 1)
            ->where('resources.0.title', 'Laravel docs'));

    $this->get(route('resources.show', $resource))
        ->assertOk()
        ->assertSee('Laravel docs');
});

test('guests cannot view draft resources', function () {
    $resource = Resource::factory()->create();

    $this->get(route('resources.show', $resource))->assertForbidden();
    $this->get(route('resources.index'))->assertDontSee($resource->title_en);
});

test('staff can view draft resources', function () {
    $resource = Resource::factory()->create(['title_en' => 'Hidden notes']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('resources.show', $resource))
        ->assertOk()
        ->assertSee('Hidden notes');
});

test('published resources can be filtered by kind', function () {
    Resource::factory()->published()->video()->create(['title_en' => 'Keynote']);
    Resource::factory()->published()->create(['title_en' => 'Docs']);

    $this->get(route('resources.index', ['kind' => 'video']))
        ->assertOk()
        ->assertSee('Keynote')
        ->assertDontSee('Docs');
});
