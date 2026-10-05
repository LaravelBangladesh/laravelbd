<?php

use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot open the profile form', function () {
    $this->get(route('account.directory.edit'))->assertRedirect(route('login'));
});

test('members see their own profile including their mobile number', function () {
    $member = User::factory()->withCompleteProfile()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($member)
        ->get(route('account.directory.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/directory')
            ->where('profile.name', 'Ada Lovelace')
            ->where('profile.mobile_number', $member->mobile_number)
            ->where('profile.directory_status', 'hidden')
            ->where('profile.is_listed', false));
});

test('members save their profile on their user without entering the directory', function () {
    $member = User::factory()->create(['name' => 'Ada Lovelace']);

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'title' => 'Mathematician',
            'city' => 'Dhaka',
            'github' => 'ada',
            'mobile_number' => '01712-345678',
            'directory_status' => 'listed',
        ])
        ->assertRedirect(route('account.directory.edit'));

    $member->refresh();

    expect($member->title)->toBe('Mathematician')
        ->and($member->github)->toBe('ada')
        ->and($member->mobile_number)->toBe('+8801712345678')
        ->and($member->slug)->toBe('ada-lovelace')
        ->and($member->directory_status)->toBe(DirectoryVisibility::Hidden);

    $this->get(route('directory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('listings', []));

    $this->actingAs($member)
        ->get(route('directory.show', 'ada-lovelace'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('directory/show')
            ->where('is_owner', true)
            ->where('is_published', false));
});

test('a mobile number in any country format is stored as E.164', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'mobile_number' => '09876 543210',
            'mobile_number_country' => 'in',
        ])
        ->assertSessionHasNoErrors();

    expect($member->fresh()?->mobile_number)->toBe('+919876543210');
});

test('the mobile number must be a valid mobile for the chosen country', function (string $number, string $country) {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'mobile_number' => $number,
            'mobile_number_country' => $country,
        ])
        ->assertSessionHasErrors(['mobile_number' => __('validation.phone')]);

    expect($member->fresh()?->mobile_number)->toBeNull();
})->with([
    'a landline' => ['02-9876543', 'BD'],
    'too short' => ['0171234', 'BD'],
    'not a number' => ['call me', 'BD'],
    'another country' => ['+447911123456', 'BD'],
]);

test('a mobile number can belong to only one account', function () {
    User::factory()->create(['mobile_number' => '+8801712345678']);
    $member = User::factory()->create(['mobile_number' => '+8801812345678']);

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'mobile_number' => '01712345678',
        ])
        ->assertSessionHasErrors(['mobile_number' => __('validation.mobile_taken')]);

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'mobile_number' => '01812-345678',
        ])
        ->assertSessionHasNoErrors();
});

test('editing a listed profile keeps it listed', function () {
    $member = User::factory()->listedInDirectory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.update'), ['name' => 'Ada Lovelace'])
        ->assertRedirect(route('account.directory.edit'));

    expect($member->fresh()?->directory_status)->toBe(DirectoryVisibility::Listed);
});

test('a profile slug never takes one held by a company', function () {
    Company::factory()->create(['slug' => 'ada-lovelace']);
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.update'), ['name' => 'Ada Lovelace']);

    expect($member->fresh()?->slug)->toBe('ada-lovelace-2');
});

test('members ask to be listed, withdraw, and hide themselves', function () {
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.visibility'), ['visibility' => 'pending'])
        ->assertRedirect(route('account.directory.edit'))
        ->assertInertiaFlash('toast.message', __('account.directory_requested'));

    expect($member->fresh()?->directory_status)->toBe(DirectoryVisibility::Pending);

    $this->actingAs($member)
        ->patch(route('account.directory.visibility'), ['visibility' => 'pending'])
        ->assertSessionHasErrors('visibility');

    $this->actingAs($member)
        ->patch(route('account.directory.visibility'), ['visibility' => 'hidden'])
        ->assertRedirect(route('account.directory.edit'))
        ->assertInertiaFlash('toast.message', __('account.directory_hidden'));

    expect($member->fresh()?->directory_status)->toBe(DirectoryVisibility::Hidden);
});

test('members cannot list themselves', function () {
    $member = User::factory()->create();
    $pending = User::factory()->pendingInDirectory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.visibility'), ['visibility' => 'listed'])
        ->assertSessionHasErrors('visibility');

    $this->actingAs($pending)
        ->patch(route('account.directory.visibility'), ['visibility' => 'listed'])
        ->assertSessionHasErrors('visibility');

    expect($member->fresh()?->directory_status)->toBe(DirectoryVisibility::Hidden)
        ->and($pending->fresh()?->directory_status)->toBe(DirectoryVisibility::Pending);
});

test('a listed member can hide from the directory', function () {
    $member = User::factory()->listedInDirectory()->create();

    $this->actingAs($member)
        ->patch(route('account.directory.visibility'), ['visibility' => 'hidden'])
        ->assertRedirect(route('account.directory.edit'));

    $member->refresh();

    expect($member->directory_status)->toBe(DirectoryVisibility::Hidden)
        ->and($member->directory_published_at)->toBeNull();

    $this->actingAs(User::factory()->create())
        ->get(route('directory.show', (string) $member->slug))
        ->assertForbidden();
});

test('members can attach a photo larger than the old five megabyte cap', function () {
    Storage::fake('public');

    $member = User::factory()->create(['name' => 'Ada Lovelace']);
    $photo = UploadedFile::fake()->image('portrait.png', 120, 120)->size(6000);

    $this->actingAs($member)
        ->patch(route('account.directory.update'), [
            'name' => 'Ada Lovelace',
            'photo' => $photo,
        ])
        ->assertRedirect(route('account.directory.edit'));

    $path = (string) $member->fresh()?->photo_path;

    expect($path)->not->toBe('')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

test('the account page shows the directory status', function () {
    $member = User::factory()->create(['slug' => null]);

    $this->actingAs($member)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/edit')
            ->where('directory.slug', null)
            ->where('directory.directory_status', 'hidden'));

    $listed = User::factory()->listedInDirectory()->create(['slug' => 'ada-lovelace']);

    $this->actingAs($listed)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('directory.slug', 'ada-lovelace')
            ->where('directory.is_listed', true));
});
