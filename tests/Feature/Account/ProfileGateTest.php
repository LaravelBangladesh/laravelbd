<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('an incomplete profile is sent to the editor instead of registering', function () {
    $event = Event::factory()->published()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('events.rsvp.store', $event))
        ->assertRedirect(route('account.directory.edit'))
        ->assertSessionHas('profile.return_to', [
            'route' => 'events.show',
            'slug' => $event->slug,
        ]);

    expect(EventRegistration::query()->count())->toBe(0);
});

test('the editor lists the missing fields and the pending destination', function () {
    $event = Event::factory()->published()->create();
    $member = User::factory()->create();

    $this->actingAs($member)->post(route('events.rsvp.store', $event));

    $this->actingAs($member)
        ->get(route('account.directory.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/directory')
            ->where('missing', ['photo', 'title', 'company'])
            ->where('return_to.label', __('profile.return_to.event')));
});

test('the editor reports no pending destination by default', function () {
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->get(route('account.directory.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('missing', [])
            ->where('return_to', null));
});

test('saving a complete profile returns the member to the event', function () {
    Storage::fake('public');

    $event = Event::factory()->published()->create();
    $member = User::factory()->create();
    DirectoryListing::factory()->create(['user_id' => $member->id]);

    $this->actingAs($member)->post(route('events.rsvp.store', $event));

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'title' => 'Mathematician',
            'company' => 'Analytical Co',
            'photo' => UploadedFile::fake()->image('ada.jpg'),
        ])
        ->assertRedirect(route('events.show', $event))
        ->assertSessionMissing('profile.return_to');
});

test('a new listing that completes the profile returns the member to the cfp form', function () {
    Storage::fake('public');

    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->create();

    $this->actingAs($member)->get(route('events.cfp.create', $event));

    $this->actingAs($member)
        ->post(route('account.directory.store'), [
            'name' => 'Ada Lovelace',
            'title' => 'Mathematician',
            'company' => 'Analytical Co',
            'photo' => UploadedFile::fake()->image('ada.jpg'),
        ])
        ->assertRedirect(route('events.cfp.create', $event));
});

test('saving a still incomplete profile keeps the member on the editor', function () {
    $event = Event::factory()->published()->create();
    $member = User::factory()->create();
    DirectoryListing::factory()->create(['user_id' => $member->id]);

    $this->actingAs($member)->post(route('events.rsvp.store', $event));

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'title' => 'Mathematician',
        ])
        ->assertRedirect(route('account.directory.edit'))
        ->assertSessionHas('profile.return_to');
});

test('an unknown pending route is ignored', function () {
    $member = User::factory()->withCompleteProfile()->create();

    $this->withSession(['profile.return_to' => ['route' => 'home', 'slug' => 'x']])
        ->actingAs($member)
        ->get(route('account.directory.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('return_to', null));
});

test('an incomplete profile is sent to the editor instead of the cfp form', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('events.cfp.create', $event))
        ->assertRedirect(route('account.directory.edit'))
        ->assertSessionHas('profile.return_to', [
            'route' => 'events.cfp.create',
            'slug' => $event->slug,
        ]);
});

test('an incomplete profile cannot post a proposal', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'abstract_en' => 'How we test Laravel apps.',
            'kind' => 'talk',
        ])
        ->assertRedirect(route('account.directory.edit'));

    expect(TalkProposal::query()->count())->toBe(0);
});

test('cancelling a registration is never gated on the profile', function () {
    $event = Event::factory()->published()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)->post(route('events.rsvp.store', $event));

    $member->directoryListing?->forceFill(['photo_path' => null])->save();

    $this->actingAs($member)
        ->delete(route('events.rsvp.destroy', $event))
        ->assertRedirect()
        ->assertSessionMissing('profile.return_to');

    expect(EventRegistration::query()->where('user_id', $member->id)->first()?->status->value)
        ->toBe('cancelled');
});

test('the event page reports whether the viewer profile is complete', function () {
    $event = Event::factory()->published()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page->where('event.viewer.profile_complete', false));

    $this->actingAs(User::factory()->withCompleteProfile()->create())
        ->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page->where('event.viewer.profile_complete', true));
});

test('the event page reports no viewer for a guest', function () {
    $event = Event::factory()->published()->create();

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page->where('event.viewer', null));
});
