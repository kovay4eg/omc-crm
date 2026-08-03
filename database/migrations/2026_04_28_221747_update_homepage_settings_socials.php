<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {

            $table->dropColumn(['x_enabled', 'x_url']);

            $table->boolean('youtube_enabled')->default(false);
            $table->string('youtube_url')->nullable();

            $table->boolean('tiktok_enabled')->default(false);
            $table->string('tiktok_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {

            // повертаємо X назад
            $table->boolean('x_enabled')->default(false);
            $table->string('x_url')->nullable();

            // видаляємо youtube і tiktok
            $table->dropColumn([
                'youtube_enabled',
                'youtube_url',
                'tiktok_enabled',
                'tiktok_url',
            ]);
        });
    }
};
