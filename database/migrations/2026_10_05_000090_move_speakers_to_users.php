<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A speaker is a user, so event and session rosters and resource speakers
 * point at users and the separate speakers table goes away.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('speakers')->exists()) {
            throw new RuntimeException(
                'Speakers cannot move onto users automatically because they have no email. '
                .'Give each speaker a user, assign that user to their events, sessions and resources, '
                .'delete the speakers rows, then run the migration again.'
            );
        }

        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['speaker_id']);
            $table->foreign('speaker_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::drop('session_speaker');
        Schema::drop('event_speaker');

        Schema::create('event_speaker', function (Blueprint $table) {
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('speaker');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });

        Schema::create('session_speaker', function (Blueprint $table) {
            $table->foreignUuid('session_id')->constrained('event_sessions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('speaker');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['session_id', 'user_id']);
        });

        Schema::drop('speakers');
    }

    public function down(): void
    {
        Schema::create('speakers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_bn')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('x')->nullable();
            $table->timestamps();
        });

        // Users are not speakers rows, so resource speakers cannot carry over.
        DB::table('resources')->update(['speaker_id' => null]);

        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['speaker_id']);
            $table->foreign('speaker_id')->references('id')->on('speakers')->nullOnDelete();
        });

        Schema::drop('session_speaker');
        Schema::drop('event_speaker');

        Schema::create('event_speaker', function (Blueprint $table) {
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('speaker_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('speaker');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['event_id', 'speaker_id']);
        });

        Schema::create('session_speaker', function (Blueprint $table) {
            $table->foreignUuid('session_id')->constrained('event_sessions')->cascadeOnDelete();
            $table->foreignUuid('speaker_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('speaker');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['session_id', 'speaker_id']);
        });
    }
};
