<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// A later migration indexes columns this one adds, so it rolls back first.
beforeEach(function () {
    (require database_path('migrations/2026_10_06_000100_add_admin_list_indexes.php'))->down();
});

function profilesMigration(): object
{
    return require database_path('migrations/2026_10_05_000080_move_directory_profiles_to_users.php');
}

function insertLegacyUser(string $name, string $email): string
{
    $id = (string) Str::uuid7();

    DB::table('users')->insert([
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'role' => 'member',
        'locale' => 'en',
    ]);

    return $id;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function insertListing(array $attributes): string
{
    $id = (string) Str::uuid7();

    DB::table('directory_listings')->insert([
        'id' => $id,
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
        ...$attributes,
    ]);

    return $id;
}

test('person listings move onto their users and companies get their own table', function () {
    $migration = profilesMigration();
    $migration->down();

    $published = insertLegacyUser('Ada Account', 'ada@example.com');
    $draft = insertLegacyUser('', 'grace@example.com');
    $bare = insertLegacyUser('No Listing', 'none@example.com');
    $publishedAt = now()->subWeek()->startOfSecond();

    insertListing([
        'slug' => 'ada-lovelace',
        'kind' => 'person',
        'status' => 'published',
        'name' => 'Ada Listing',
        'title' => 'Mathematician',
        'company' => 'Analytical Co',
        'city' => 'Dhaka',
        'bio_en' => 'Bio',
        'photo_path' => 'directory/ada.jpg',
        'github' => 'ada',
        'published_at' => $publishedAt,
        'user_id' => $published,
    ]);
    insertListing([
        'slug' => 'grace-hopper',
        'kind' => 'person',
        'name' => 'Grace Hopper',
        'user_id' => $draft,
    ]);
    $companyId = insertListing([
        'slug' => 'engines',
        'kind' => 'company',
        'status' => 'published',
        'name' => 'Engines',
        'title' => 'Software studio',
        'company' => 'ignored',
        'website' => 'https://engines.test',
        'published_at' => $publishedAt,
    ]);

    $migration->up();

    $ada = DB::table('users')->where('id', $published)->first();
    $grace = DB::table('users')->where('id', $draft)->first();
    $company = DB::table('companies')->where('id', $companyId)->first();

    expect(Schema::hasTable('directory_listings'))->toBeFalse()
        ->and($ada->name)->toBe('Ada Account')
        ->and($ada->slug)->toBe('ada-lovelace')
        ->and($ada->title)->toBe('Mathematician')
        ->and($ada->company)->toBe('Analytical Co')
        ->and($ada->photo_path)->toBe('directory/ada.jpg')
        ->and($ada->github)->toBe('ada')
        ->and($ada->directory_status)->toBe('listed')
        ->and($ada->directory_published_at)->not->toBeNull()
        ->and($grace->name)->toBe('Grace Hopper')
        ->and($grace->directory_status)->toBe('pending')
        ->and(DB::table('users')->where('id', $bare)->value('directory_status'))->toBe('hidden')
        ->and($company->slug)->toBe('engines')
        ->and($company->status)->toBe('published')
        ->and($company->title)->toBe('Software studio')
        ->and($company->website)->toBe('https://engines.test');

    $migration->down();

    $listings = DB::table('directory_listings')->orderBy('slug')->get();

    expect(Schema::hasColumn('users', 'slug'))->toBeFalse()
        ->and(Schema::hasTable('companies'))->toBeFalse()
        ->and($listings->pluck('slug')->all())->toBe(['ada-lovelace', 'engines', 'grace-hopper'])
        ->and($listings[0]->kind)->toBe('person')
        ->and($listings[0]->status)->toBe('published')
        ->and($listings[0]->user_id)->toBe($published)
        ->and($listings[0]->title)->toBe('Mathematician')
        ->and($listings[1]->id)->toBe($companyId)
        ->and($listings[1]->kind)->toBe('company')
        ->and($listings[2]->status)->toBe('draft');

    $migration->up();
});

test('the migration stops and names person listings that have no user', function () {
    $migration = profilesMigration();
    $migration->down();

    insertListing(['slug' => 'zed-orphan', 'kind' => 'person', 'name' => 'Zed']);
    insertListing(['slug' => 'amy-orphan', 'kind' => 'person', 'name' => 'Amy']);

    expect(fn () => $migration->up())->toThrow(
        RuntimeException::class,
        'Person directory listings without a user cannot move onto users: amy-orphan, zed-orphan.',
    );

    expect(Schema::hasTable('directory_listings'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'slug'))->toBeFalse();

    DB::table('directory_listings')->delete();
    $migration->up();
});
