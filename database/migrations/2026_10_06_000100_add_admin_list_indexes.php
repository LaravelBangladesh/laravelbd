<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the paginated admin lists: the user filters, an event's
 * attendees by status, and the answers loaded for each page of attendees.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('directory_status');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->index(['event_id', 'status']);
        });

        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->index('event_registration_id');
        });
    }

    public function down(): void
    {
        Schema::table('event_registration_answers', function (Blueprint $table) {
            $table->dropIndex(['event_registration_id']);
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['directory_status']);
        });
    }
};
