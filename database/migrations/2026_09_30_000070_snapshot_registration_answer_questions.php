<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Registration answers keep a copy of the question they answered, so editing
 * or deleting an event question never changes what an attendee was asked.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->json('question')->nullable()->after('event_registration_id');
        });

        DB::table('event_registration_answers')
            ->join('event_questions', 'event_questions.id', '=', 'event_registration_answers.event_question_id')
            ->select([
                'event_registration_answers.id as answer_id',
                'event_questions.id',
                'event_questions.kind',
                'event_questions.label_en',
                'event_questions.label_bn',
                'event_questions.help_en',
                'event_questions.help_bn',
                'event_questions.options',
                'event_questions.required',
            ])
            ->lazyById(500, 'event_registration_answers.id', 'answer_id')
            ->each(function (object $row) {
                DB::table('event_registration_answers')
                    ->where('id', $row->answer_id)
                    ->update(['question' => json_encode([
                        'id' => $row->id,
                        'kind' => $row->kind,
                        'label_en' => $row->label_en,
                        'label_bn' => $row->label_bn,
                        'help_en' => $row->help_en,
                        'help_bn' => $row->help_bn,
                        'options' => $row->options === null ? null : json_decode($row->options, true),
                        'required' => (bool) $row->required,
                    ])]);
            });

        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->dropUnique('event_answer_unique');
        });

        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_question_id');
        });

        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->json('question')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->foreignUuid('event_question_id')->nullable()->after('event_registration_id')
                ->constrained()->cascadeOnDelete();
        });

        $questionIds = DB::table('event_questions')->pluck('id')->all();

        // Answers whose question no longer exists cannot point back at it, so
        // they go, as the old cascading foreign key would have removed them.
        DB::table('event_registration_answers')->lazyById(500)->each(function (object $row) use ($questionIds) {
            $questionId = json_decode($row->question, true)['id'] ?? null;
            $answer = DB::table('event_registration_answers')->where('id', $row->id);

            if (in_array($questionId, $questionIds, true)) {
                $answer->update(['event_question_id' => $questionId]);
            } else {
                $answer->delete();
            }
        });

        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->unique(['event_registration_id', 'event_question_id'], 'event_answer_unique');
            $table->dropColumn('question');
        });
    }
};
