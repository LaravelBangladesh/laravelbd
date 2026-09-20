<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('kind');
            $table->string('status')->default('draft');
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->string('excerpt_en')->nullable();
            $table->string('excerpt_bn')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('url')->nullable();
            $table->string('embed_url')->nullable();
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
