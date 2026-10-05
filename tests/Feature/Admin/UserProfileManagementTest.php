<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot edit another member profile', function () {
    $member = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($member)->get(route('admin.users.edit', $other))->assertForbidden();
    $this->actingAs($member)
        ->patch(route('admin.users.update', $other), ['name' => 'Hijacked', 'directory_status' => 'listed'])
        ->assertForbidden();

    expect($other->fresh()?->name)->not->toBe('Hijacked');
});

test('staff see mobile numbers and directory status in the users list', function () {
    $moderator = User::factory()->moderator()->create(['name' => 'Zed Moderator']);
    $member = User::factory()->pendingInDirectory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($moderator)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/index')
            ->where('users.0.name', 'Ada Lovelace')
            ->where('users.0.mobile_number', $member->mobile_number)
            ->where('users.0.directory_status', 'pending')
            ->where('users.0.directory_status_label', __('directory.visibility.pending')));
});

test('staff edit a member profile and approve their listing', function () {
    $moderator = User::factory()->moderator()->create();
    $member = User::factory()->pendingInDirectory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($moderator)
        ->get(route('admin.users.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/edit')
            ->where('profile.id', $member->id)
            ->where('profile.mobile_number', $member->mobile_number)
            ->where('profile.directory_status', 'pending')
            ->has('visibilities', 3));

    $this->actingAs($moderator)
        ->patch(route('admin.users.update', $member), [
            'name' => 'Ada Lovelace',
            'title' => 'Principal Engineer',
            'company' => 'Analytical Co',
            'mobile_number' => '01812345678',
            'directory_status' => 'listed',
        ])
        ->assertRedirect(route('admin.users.edit', $member));

    $member->refresh();

    expect($member->title)->toBe('Principal Engineer')
        ->and($member->mobile_number)->toBe('+8801812345678')
        ->and($member->directory_status)->toBe(DirectoryVisibility::Listed)
        ->and($member->directory_published_at)->not->toBeNull();

    $this->get(route('directory.show', (string) $member->slug))->assertOk();
});

test('staff can hide a listed member', function () {
    $moderator = User::factory()->moderator()->create();
    $member = User::factory()->listedInDirectory()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.users.update', $member), [
            'name' => $member->name,
            'directory_status' => 'hidden',
        ])
        ->assertRedirect();

    expect($member->fresh()?->directory_status)->toBe(DirectoryVisibility::Hidden);
});

test('staff cannot give a member a number another account uses', function () {
    $moderator = User::factory()->moderator()->create(['mobile_number' => '+8801712345678']);
    $member = User::factory()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.users.update', $member), [
            'name' => 'Ada Lovelace',
            'mobile_number' => '+8801712345678',
            'directory_status' => 'hidden',
        ])
        ->assertSessionHasErrors('mobile_number');
});

test('staff see the submitter mobile number on a proposal', function () {
    $submitter = User::factory()->withCompleteProfile()->create();
    $proposal = TalkProposal::factory()->create(['user_id' => $submitter->id]);

    $this->actingAs(User::factory()->moderator()->create())
        ->get(route('admin.proposals.show', $proposal))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('proposal.submitter.mobile_number', $submitter->mobile_number));
});
