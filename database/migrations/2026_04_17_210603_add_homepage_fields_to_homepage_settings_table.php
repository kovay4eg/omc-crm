<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->string('logo')->nullable();

            $table->boolean('facebook_enabled')->default(false);
            $table->string('facebook_url')->nullable();

            $table->boolean('instagram_enabled')->default(false);
            $table->string('instagram_url')->nullable();

            $table->boolean('telegram_enabled')->default(false);
            $table->string('telegram_url')->nullable();

            $table->boolean('x_enabled')->default(false);
            $table->string('x_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo',

                'facebook_enabled',
                'facebook_url',

                'instagram_enabled',
                'instagram_url',

                'telegram_enabled',
                'telegram_url',

                'x_enabled',
                'x_url',
            ]);
        });
    }
};
