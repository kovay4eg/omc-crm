<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->string('source', 40)->nullable()->after('user_agent')->index();
            $table->string('device_name', 120)->nullable()->after('source');
            $table->string('platform', 50)->nullable()->after('device_name');
        });
    }

    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn(['source', 'device_name', 'platform']);
        });
    }
};
