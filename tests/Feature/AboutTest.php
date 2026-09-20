<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view the about page', function () {
    Event::factory()->published()->create();
    Speaker::factory()->create();

    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('about')
            ->where('stats.events', 1)
            ->where('stats.speakers', 1)
            ->where(
                'translations',
                fn ($translations) => $translations['about.hero.title'] === 'The Laravel community of Bangladesh since 2012'
            )
        );
});

test('about page uses bangla copy', function () {
    $this->post(route('locale.update'), ['locale' => 'bn']);

    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where(
                'translations',
                fn ($translations) => $translations['about.hero.title'] === '২০১২ থেকে বাংলাদেশের Laravel কমিউনিটি'
            )
        );
});
