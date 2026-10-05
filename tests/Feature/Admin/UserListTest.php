<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, string>  $query
 * @return list<string>
 */
function listedUserNames(mixed $test, User $viewer, array $query): array
{
    $names = [];

    $test->actingAs($viewer)
        ->get(route('admin.users.index', $query))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$names) {
            $names = array_column($page->toArray()['props']['users']['data'], 'name');
        });

    return $names;
}

test('the users list shows 25 people a page and keeps the filters in its links', function () {
    $moderator = User::factory()->moderator()->create(['name' => 'Zed Moderator']);
    User::factory()->count(30)->create();

    $this->actingAs($moderator)
        ->get(route('admin.users.index', ['role' => 'member']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/index')
            ->has('users.data', 25)
            ->where('users.total', 30)
            ->where('users.last_page', 2)
            ->where('users.next_page_url', fn (string $url) => str_contains($url, 'role=member') && str_contains($url, 'page=2'))
            ->where('filters', ['q' => '', 'role' => 'member', 'directory_status' => '', 'speaker' => '', 'status' => ''])
            ->has('roles', 3)
            ->has('visibilities', 3));

    $this->actingAs($moderator)
        ->get(route('admin.users.index', ['role' => 'member', 'page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('users.data', 5));
});

test('staff search users by name, email or mobile number', function (string $term) {
    $moderator = User::factory()->moderator()->create(['name' => 'Zed Moderator', 'email' => 'zed@example.com']);
    User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@analytical.test', 'mobile_number' => '+8801712345678']);
    User::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@navy.test', 'mobile_number' => '+8801812345678']);

    expect(listedUserNames($this, $moderator, ['q' => $term]))->toBe(['Ada Lovelace']);
})->with([
    'name, any case' => 'lovelace',
    'email' => 'ANALYTICAL',
    'mobile in E.164' => '+8801712',
    'mobile digits without the plus' => '8801712345678',
    'mobile in local format' => '01712-345678',
]);

test('staff filter users by role, directory status and speaking', function () {
    $moderator = User::factory()->moderator()->create(['name' => 'Zed Moderator']);
    User::factory()->listedInDirectory()->create(['name' => 'Ada Lovelace']);
    $speaker = User::factory()->pendingInDirectory()->create(['name' => 'Grace Hopper']);
    TalkProposal::factory()->accepted()->create([
        'user_id' => $speaker->id,
        'event_id' => Event::factory()->create(['created_by' => $moderator->id])->id,
    ]);

    expect(listedUserNames($this, $moderator, ['role' => 'moderator']))->toBe(['Zed Moderator'])
        ->and(listedUserNames($this, $moderator, ['directory_status' => DirectoryVisibility::Listed->value]))->toBe(['Ada Lovelace'])
        ->and(listedUserNames($this, $moderator, ['directory_status' => DirectoryVisibility::Hidden->value]))->toBe(['Zed Moderator'])
        ->and(listedUserNames($this, $moderator, ['speaker' => 'yes']))->toBe(['Grace Hopper'])
        ->and(listedUserNames($this, $moderator, ['speaker' => 'no']))->toBe(['Ada Lovelace', 'Zed Moderator'])
        ->and(listedUserNames($this, $moderator, ['q' => '  ']))->toBe(['Ada Lovelace', 'Grace Hopper', 'Zed Moderator']);
});

test('an unknown filter value is rejected', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.users.index', ['role' => 'owner', 'speaker' => 'maybe']))
        ->assertSessionHasErrors(['role', 'speaker']);
});

test('staff export the filtered users as csv', function () {
    $moderator = User::factory()->moderator()->create(['name' => 'Zed Moderator']);
    User::factory()->pendingInDirectory()->create([
        'name' => '=HYPERLINK("https://evil.test")',
        'email' => 'ada@example.com',
        'mobile_number' => '+8801712345678',
    ]);
    User::factory()->create(['name' => 'Grace Hopper']);

    $response = $this->actingAs($moderator)
        ->get(route('admin.users.export', ['directory_status' => 'pending']));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertDownload('users-'.now()->toDateString().'.csv');

    expect(array_map(str_getcsv(...), explode("\n", trim(csvBody($response)))))->toBe([
        [__('auth.name'), __('auth.email'), 'Mobile', 'Role', 'Directory', 'Joined'],
        ["'=HYPERLINK(\"https://evil.test\")", 'ada@example.com', '+8801712345678', 'Member', __('directory.visibility.pending'), now()->toDateString()],
    ]);
});

test('members cannot list or export users', function () {
    $member = User::factory()->create();

    $this->actingAs($member)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($member)->get(route('admin.users.export'))->assertForbidden();
});
