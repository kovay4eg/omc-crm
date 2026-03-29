<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->string('status')->default('draft')->change();

            $table->text('cancel_reason')->nullable();
            $table->boolean('cancel_public')->default(false);
            $table->timestamp('cancelled_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->dropColumn([
                'cancel_reason',
                'cancel_public',
                'cancelled_at',
            ]);

        });
    }
};