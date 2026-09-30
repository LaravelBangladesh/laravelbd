<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->json('cfp_questions')->nullable()->after('cfp_closes_at');
        });

        Schema::table('talk_proposals', function (Blueprint $table) {
            $table->json('answers')->nullable()->after('abstract_bn');
        });
    }

    public function down(): void
    {
        Schema::table('talk_proposals', function (Blueprint $table) {
            $table->dropColumn('answers');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('cfp_questions');
        });
    }
};
