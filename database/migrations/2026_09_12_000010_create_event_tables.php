<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('status')->default('draft');
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('excerpt_bn')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('venue_map_url')->nullable();
            $table->string('online_url')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('registration_enabled')->default(true);
            $table->boolean('cfp_enabled')->default(false);
            $table->timestamp('cfp_opens_at')->nullable();
            $table->timestamp('cfp_closes_at')->nullable();
            $table->string('cover_path')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });

        Schema::create('event_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('kind');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('room')->nullable();
            $table->string('recording_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

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

        Schema::create('event_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->string('path')->nullable();
            $table->string('embed_url')->nullable();
            $table->string('caption_en')->nullable();
            $table->string('caption_bn')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered');
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });

        Schema::create('event_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->string('label_en');
            $table->string('label_bn')->nullable();
            $table->text('help_en')->nullable();
            $table->text('help_bn')->nullable();
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('event_registration_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_question_id')->constrained()->cascadeOnDelete();
            $table->json('value');
            $table->timestamps();

            $table->unique(['event_registration_id', 'event_question_id'], 'event_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_answers');
        Schema::dropIfExists('event_questions');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_media');
        Schema::dropIfExists('session_speaker');
        Schema::dropIfExists('event_speaker');
        Schema::dropIfExists('event_sessions');
        Schema::dropIfExists('events');
        Schema::dropIfExists('speakers');
    }
};
