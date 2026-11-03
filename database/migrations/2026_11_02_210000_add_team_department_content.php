<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('slider_enabled')->default(false);
            $table->json('slider_photos')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', fn (Blueprint $table) => $table->dropColumn('description'));
        Schema::table('departments', fn (Blueprint $table) => $table->dropColumn([
            'description', 'photo', 'slider_enabled', 'slider_photos',
        ]));
    }
};
