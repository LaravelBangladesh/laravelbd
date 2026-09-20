<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directory_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('kind');
            $table->string('status')->default('draft');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_bn')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('github')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('x')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directory_listings');
    }
};
