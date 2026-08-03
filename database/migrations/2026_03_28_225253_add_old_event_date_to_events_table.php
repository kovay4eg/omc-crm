php artisan make:migration add_old_event_date_to_events_table<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 🔼 ДОДАЄМО ПОЛЕ
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {

            // 🔥 стара дата перед перенесенням
            $table->timestamp('old_event_date')->nullable();

        });
    }

    /**
     * 🔽 ВІДКАТ
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->dropColumn('old_event_date');

        });
    }
};
