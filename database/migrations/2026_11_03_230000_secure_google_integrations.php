<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('google_email')->nullable()->after('google_token_expires_at');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->text('google_sheet_url')->nullable()->after('google_form_url');
            $table->string('google_sheet_name')->nullable()->after('google_sheet_url');
            $table->foreignId('google_sync_user_id')
                ->nullable()
                ->after('google_sheet_name')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('google_last_synced_at')->nullable()->after('google_sync_user_id');
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->string('google_row_key', 64)->nullable()->after('external_url');
            $table->unique(
                ['event_id', 'google_row_key'],
                'registrations_event_google_row_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropUnique('registrations_event_google_row_unique');
            $table->dropColumn('google_row_key');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('google_sync_user_id');
            $table->dropColumn([
                'google_sheet_url',
                'google_sheet_name',
                'google_last_synced_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('google_email');
        });
    }
};
