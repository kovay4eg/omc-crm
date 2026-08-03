<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->text('google_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->timestamp('google_token_expires_at')->nullable();
        });

        // EVENTS
        Schema::table('events', function (Blueprint $table) {
            $table->string('google_event_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_token',
                'google_refresh_token',
                'google_token_expires_at',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });
    }
};
