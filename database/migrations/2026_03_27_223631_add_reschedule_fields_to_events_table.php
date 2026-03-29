<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->timestamp('rescheduled_at')->nullable();

            $table->text('reschedule_reason')->nullable();

            $table->boolean('reschedule_public')->default(false);

        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->dropColumn([
                'rescheduled_at',
                'reschedule_reason',
                'reschedule_public'
            ]);

        });
    }
};