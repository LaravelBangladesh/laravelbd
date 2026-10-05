<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

function speakersMigration(): object
{
    return require database_path('migrations/2026_10_05_000090_move_speakers_to_users.php');
}

/**
 * @return list<string>
 */
function foreignTables(string $table, string $column): array
{
    return collect(Schema::getForeignKeys($table))
        ->filter(fn (array $key) => $key['columns'] === [$column])
        ->pluck('foreign_table')
        ->values()
        ->all();
}

test('rosters and resource speakers point at users and speakers go away', function () {
    $migration = speakersMigration();
    $migration->down();

    expect(Schema::hasTable('speakers'))->toBeTrue()
        ->and(Schema::hasColumn('event_speaker', 'speaker_id'))->toBeTrue()
        ->and(Schema::hasColumn('session_speaker', 'speaker_id'))->toBeTrue()
        ->and(foreignTables('resources', 'speaker_id'))->toBe(['speakers']);

    $migration->up();

    expect(Schema::hasTable('speakers'))->toBeFalse()
        ->and(Schema::getColumnListing('event_speaker'))->toEqualCanonicalizing(['event_id', 'user_id', 'role', 'sort_order', 'created_at', 'updated_at'])
        ->and(Schema::getColumnListing('session_speaker'))->toEqualCanonicalizing(['session_id', 'user_id', 'role', 'sort_order', 'created_at', 'updated_at'])
        ->and(foreignTables('event_speaker', 'user_id'))->toBe(['users'])
        ->and(foreignTables('session_speaker', 'user_id'))->toBe(['users'])
        ->and(foreignTables('resources', 'speaker_id'))->toBe(['users']);
});

test('the migration stops while speakers rows still exist', function () {
    $migration = speakersMigration();
    $migration->down();

    DB::table('speakers')->insert([
        'id' => (string) Str::uuid7(),
        'name' => 'Ada Lovelace',
        'slug' => 'ada-lovelace',
    ]);

    expect(fn () => $migration->up())->toThrow(
        RuntimeException::class,
        'Speakers cannot move onto users automatically because they have no email.',
    );

    expect(Schema::hasTable('speakers'))->toBeTrue()
        ->and(Schema::hasColumn('event_speaker', 'speaker_id'))->toBeTrue();

    DB::table('speakers')->delete();
    $migration->up();
});
