<?php

use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('the migration backfills answer snapshots and rolls back to question ids', function () {
    $migration = require database_path('migrations/2026_09_30_000070_snapshot_registration_answer_questions.php');
    $migration->down();

    $question = EventQuestion::factory()->multipleChoice()->create(['label_en' => 'Topics']);
    $registration = EventRegistration::factory()->create(['event_id' => $question->event_id]);
    $answerId = (string) Str::uuid7();
    DB::table('event_registration_answers')->insert([
        'id' => $answerId,
        'event_registration_id' => $registration->id,
        'event_question_id' => $question->id,
        'value' => json_encode(['APIs']),
    ]);

    $migration->up();

    expect(Schema::hasColumn('event_registration_answers', 'event_question_id'))->toBeFalse()
        ->and(json_decode(DB::table('event_registration_answers')->value('question'), true))
        ->toBe($question->snapshot());

    $registration->answers()->create([
        'question' => [...$question->snapshot(), 'id' => (string) Str::uuid7()],
        'value' => 'Gone',
    ]);
    $migration->down();

    expect(DB::table('event_registration_answers')->pluck('event_question_id')->all())->toBe([$question->id]);

    $migration->up();
});
