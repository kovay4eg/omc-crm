<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_archives', function (Blueprint $table): void {
            $table->id();
            $table->string('original_path', 512)->unique();
            $table->string('source_disk', 64)->default('public');
            $table->string('remote_path', 1024)->nullable();
            $table->uuid('remote_file_id')->nullable()->index();
            $table->uuid('remote_revision_id')->nullable();
            $table->char('sha256', 64)->nullable()->index();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime_type', 255)->nullable();
            $table->string('reason', 64)->nullable()->index();
            $table->string('source_type', 255)->nullable();
            $table->string('source_id', 128)->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->string('scan_status', 32)->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('eligible_at')->nullable()->index();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('media_archives')
            && DB::table('media_archives')->whereNotNull('remote_file_id')->exists()) {
            throw new RuntimeException(
                'Media archive metadata cannot be rolled back while remote files exist. Restore every archived file locally first.'
            );
        }

        Schema::dropIfExists('media_archives');
    }
};
