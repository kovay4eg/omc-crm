<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->string('contact_address')->nullable()->after('logo');
            $table->string('contact_phone', 50)->nullable()->after('contact_address');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('google_maps_url', 2048)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_address',
                'contact_phone',
                'contact_email',
                'google_maps_url',
            ]);
        });
    }
};
