<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->string('smm_title')->nullable();
            $table->text('smm_description')->nullable();
            $table->string('smm_image')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('smm_title')->nullable();
            $table->text('smm_description')->nullable();
            $table->string('smm_image')->nullable();
        });

        Schema::table('event_summaries', function (Blueprint $table) {
            $table->string('smm_title')->nullable();
            $table->text('smm_description')->nullable();
            $table->string('smm_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_summaries', function (Blueprint $table) {
            $table->dropColumn(['smm_title', 'smm_description', 'smm_image']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['smm_title', 'smm_description', 'smm_image']);
        });

        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn(['smm_title', 'smm_description', 'smm_image']);
        });
    }
};
