<?php

use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('visitors can switch locale', function () {
    $this->post(route('locale.update'), ['locale' => 'bn'])
        ->assertRedirect();

    expect(session('locale'))->toBe('bn');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'bn')
            ->where(
                'translations',
                fn ($translations) => $translations['home.hero.title'] === '২০১২ থেকে একসাথে বাংলাদেশের Laravel ডেভেলপাররা'
            )
        );
});

test('a signed in member has their locale preference persisted', function () {
    $member = User::factory()->create(['locale' => 'en']);

    $this->actingAs($member)
        ->post(route('locale.update'), ['locale' => 'bn'])
        ->assertRedirect();

    expect($member->fresh()?->locale)->toBe('bn')
        ->and(session('locale'))->toBe('bn');
});

test('an unsupported locale is rejected', function () {
    $this->post(route('locale.update'), ['locale' => 'fr'])
        ->assertSessionHasErrors('locale');

    expect(session('locale'))->toBeNull();
});

test('a stored locale outside the supported set falls back to the app default', function () {
    $member = User::factory()->create(['locale' => 'fr']);

    $this->actingAs($member)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('locale', config('app.locale')));
});

test('the browser preferred language is used when nothing is stored', function () {
    $this->withHeader('Accept-Language', 'bn')
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'bn'));
});
