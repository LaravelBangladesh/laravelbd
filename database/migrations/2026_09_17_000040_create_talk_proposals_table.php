<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talk_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kind');
            $table->string('status')->default('submitted');
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->text('abstract_en');
            $table->text('abstract_bn')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_session_id')->nullable()->constrained('event_sessions')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talk_proposals');
    }
};
