<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {

            // унікальний email в межах події
            $table->unique(['event_id', 'email']);

            // унікальний телефон в межах події
            $table->unique(['event_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {

            $table->dropUnique(['event_id', 'email']);
            $table->dropUnique(['event_id', 'phone']);
        });
    }
};
