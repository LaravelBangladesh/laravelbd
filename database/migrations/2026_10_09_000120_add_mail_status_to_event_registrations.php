<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track, per attendee, whether the registration confirmation and the event
 * reminder emails went out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('confirmation_status')->default('not_sent');
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->string('reminder_status')->default('not_sent');
            $table->timestamp('reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['confirmation_status', 'confirmation_sent_at', 'reminder_status', 'reminder_sent_at']);
        });
    }
};
