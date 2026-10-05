<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Mail\LoginChallengeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredential;

function deactivatedUser(array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->delete();

    return $user;
}

test('admins deactivate and reactivate a member', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $member))
        ->assertRedirect()
        ->assertInertiaFlash('toast.message', __('admin.user_deactivated'));

    expect($member->fresh()?->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.users.restore', $member))
        ->assertRedirect()
        ->assertInertiaFlash('toast.message', __('admin.user_reactivated'));

    expect($member->fresh()?->trashed())->toBeFalse();
});

test('admins cannot deactivate themselves or another admin', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
    $this->actingAs($admin)->delete(route('admin.users.destroy', $other))->assertForbidden();

    expect($admin->fresh()?->trashed())->toBeFalse()
        ->and($other->fresh()?->trashed())->toBeFalse();
});

test('moderators cannot deactivate or reactivate users', function () {
    $moderator = User::factory()->moderator()->create();
    $member = User::factory()->create();
    $gone = deactivatedUser();

    $this->actingAs($moderator)->delete(route('admin.users.destroy', $member))->assertForbidden();
    $this->actingAs($moderator)->patch(route('admin.users.restore', $gone))->assertForbidden();
});

test('only a deactivated user can be reactivated', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.restore', $member))->assertForbidden();
});

test('a deactivated user cannot be updated or given a role', function () {
    $admin = User::factory()->admin()->create();
    $gone = deactivatedUser();

    $this->actingAs($admin)->patch(route('admin.users.update', $gone), ['name' => 'Hijacked'])->assertNotFound();
    $this->actingAs($admin)->patch(route('admin.users.role', $gone), ['role' => 'admin'])->assertNotFound();
});

test('editing a deactivated user leads to their page instead', function () {
    $moderator = User::factory()->moderator()->create();
    $gone = deactivatedUser();

    $this->actingAs($moderator)
        ->get(route('admin.users.edit', $gone))
        ->assertRedirect(route('admin.users.show', $gone))
        ->assertInertiaFlash('toast.message', __('admin.user_reactivate_to_edit'));
});

test('the edit page carries the role and its options', function () {
    $moderator = User::factory()->moderator()->create();
    $member = User::factory()->create();

    $this->actingAs($moderator)
        ->get(route('admin.users.edit', $member))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/edit')
            ->where('role', 'member')
            ->has('roles', 3));
});

test('staff view a user with their registrations and proposals', function () {
    $moderator = User::factory()->moderator()->create();
    $member = deactivatedUser(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    $event = Event::factory()->create(['title_en' => 'October Meetup', 'created_by' => $moderator->id]);
    EventRegistration::factory()->create(['event_id' => $event->id, 'user_id' => $member->id]);
    TalkProposal::factory()->create(['event_id' => $event->id, 'user_id' => $member->id, 'title_en' => 'Queues']);

    $this->actingAs($moderator)
        ->get(route('admin.users.show', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/show')
            ->where('user.name', 'Ada Lovelace')
            ->where('user.email', 'ada@example.com')
            ->where('user.role_label', __('roles.member'))
            ->where('user.is_active', false)
            ->where('registrations.0.event_id', $event->id)
            ->where('registrations.0.event_title', 'October Meetup')
            ->where('registrations.0.status', 'registered')
            ->where('proposals.0.title', 'Queues'));
});

test('members cannot view a user in admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.show', User::factory()->create()))
        ->assertForbidden();
});

test('the users list shows active users unless asked otherwise', function () {
    $admin = User::factory()->admin()->create(['name' => 'Zed Admin']);
    User::factory()->create(['name' => 'Ada Lovelace']);
    deactivatedUser(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 2)
            ->where('users.data.0.name', 'Ada Lovelace')
            ->where('users.data.0.is_active', true)
            ->where('users.data.0.can_deactivate', true)
            ->where('users.data.0.can_reactivate', false)
            ->where('users.data.1.can_deactivate', false)
            ->where('filters.status', ''));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'inactive']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Grace Hopper')
            ->where('users.data.0.is_active', false)
            ->where('users.data.0.can_reactivate', true)
            ->where('filters.status', 'inactive'));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'all']))
        ->assertInertia(fn (Assert $page) => $page->has('users.data', 3));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'active']))
        ->assertInertia(fn (Assert $page) => $page->has('users.data', 2));

    $export = $this->actingAs($admin)->get(route('admin.users.export', ['status' => 'inactive']));

    expect(csvBody($export))->toContain('grace@example.com')->not->toContain('Ada Lovelace');
});

test('a deactivated user is signed out on their next request', function () {
    $member = User::factory()->create();

    $this->withSession([Auth::guard()->getName() => $member->id])
        ->get(route('account.edit'))
        ->assertOk();

    $member->delete();
    Auth::forgetGuards();

    $this->withSession([Auth::guard()->getName() => $member->id])
        ->get(route('account.edit'))
        ->assertRedirect(route('login'));
});

test('a deactivated user cannot ask for a login code', function () {
    Mail::fake();
    deactivatedUser(['email' => 'ada@example.com']);

    $this->post(route('login.email'), ['email' => 'Ada@Example.com'])
        ->assertSessionHasErrors(['email' => __('auth.account_deactivated')]);

    Mail::assertNothingQueued();
    expect(User::withTrashed()->where('email', 'ada@example.com')->count())->toBe(1);
});

test('a code sent before deactivation no longer signs in', function () {
    Mail::fake();
    $member = User::factory()->create();

    $this->post(route('login.email'), ['email' => $member->email]);
    $code = lastLoginCode();
    $member->delete();

    $this->post(route('login.code'), ['email' => $member->email, 'code' => $code])
        ->assertSessionHasErrors(['code' => __('auth.account_deactivated')]);

    $this->assertGuest();
    expect(User::withTrashed()->where('email', $member->email)->count())->toBe(1);
});

test('a magic link sent before deactivation no longer signs in', function () {
    Mail::fake();
    $member = User::factory()->create();

    $this->post(route('login.email'), ['email' => $member->email]);
    $member->delete();

    $this->post(lastMagicUrl())->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('a deactivated user\'s passkey is refused', function () {
    $member = User::factory()->create();
    $member->passkeys()->create([
        'name' => 'Laptop',
        'credential_id' => Base64UrlSafe::encodeUnpadded('raw-id'),
        'credential' => [],
    ]);
    $credential = new PublicKeyCredential('public-key', 'raw-id', Mockery::mock(AuthenticatorAssertionResponse::class));
    $verify = app(VerifyPasskey::class);

    expect($verify->getPasskey($credential)->user_id)->toBe($member->id);

    $member->delete();

    expect(fn () => $verify->getPasskey($credential))
        ->toThrow(InvalidPasskeyException::class, __('auth.account_deactivated'));
});

test('a deactivated email cannot be taken by a guest speaker or an email change', function () {
    Mail::fake();
    $moderator = User::factory()->moderator()->create();
    deactivatedUser(['email' => 'ada@example.com']);
    $member = User::factory()->create();

    $this->actingAs($moderator)
        ->post(route('admin.speakers.store'), ['name' => 'Ada Again', 'email' => 'ada@example.com'])
        ->assertSessionHasErrors(['email' => __('admin.email_deactivated')]);

    $this->actingAs($member)
        ->patch(route('account.update'), ['name' => $member->name, 'email' => 'ada@example.com', 'locale' => 'en'])
        ->assertSessionHasErrors(['email' => __('account.email_taken')]);

    Mail::assertNotQueued(LoginChallengeMail::class);
    expect(User::withTrashed()->where('email', 'ada@example.com')->count())->toBe(1);
});

test('a deactivated user leaves public pages', function () {
    $listed = User::factory()->listedInDirectory()->create(['name' => 'Ada Lovelace']);
    $event = Event::factory()->published()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $session->speakers()->attach($listed, ['role' => 'speaker']);
    $event->speakers()->attach($listed, ['role' => 'host']);
    EventRegistration::factory()->create(['event_id' => $event->id, 'user_id' => $listed->id]);

    $listed->delete();

    $this->get(route('directory.show', (string) $listed->slug))->assertNotFound();
    $this->get(route('events.show', $event))->assertOk()->assertDontSee('Ada Lovelace');
});

test('the dashboard counts only active users', function () {
    $moderator = User::factory()->moderator()->create();
    User::factory()->create();
    deactivatedUser();

    $this->actingAs($moderator)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.members', 1)
            ->where('stats.staff', 1));
});

test('deactivating a user cancels their registrations for events that have not ended', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $upcoming = Event::factory()->published()->create();
    $later = Event::factory()->published()->create();
    $past = Event::factory()->published()->create(['starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()]);
    $registered = EventRegistration::factory()->create(['event_id' => $upcoming->id, 'user_id' => $member->id]);
    $waitlisted = EventRegistration::factory()->waitlisted()->create(['event_id' => $later->id, 'user_id' => $member->id]);
    $history = EventRegistration::factory()->create(['event_id' => $past->id, 'user_id' => $member->id]);
    $cancelled = EventRegistration::factory()->cancelled()->create(['user_id' => $member->id]);
    $cancelledAt = $cancelled->updated_at;
    $someoneElse = EventRegistration::factory()->create(['event_id' => $upcoming->id]);

    $this->travel(1)->minute();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $member));

    expect($registered->fresh()?->status)->toBe(RegistrationStatus::Cancelled)
        ->and($waitlisted->fresh()?->status)->toBe(RegistrationStatus::Cancelled)
        ->and($history->fresh()?->status)->toBe(RegistrationStatus::Registered)
        ->and($cancelled->fresh()?->status)->toBe(RegistrationStatus::Cancelled)
        ->and($cancelled->fresh()?->updated_at?->equalTo($cancelledAt))->toBeTrue()
        ->and($someoneElse->fresh()?->status)->toBe(RegistrationStatus::Registered)
        ->and($upcoming->registeredCount())->toBe(1);

    $this->actingAs($admin)->patch(route('admin.users.restore', $member));

    expect($registered->fresh()?->status)->toBe(RegistrationStatus::Cancelled);
});
