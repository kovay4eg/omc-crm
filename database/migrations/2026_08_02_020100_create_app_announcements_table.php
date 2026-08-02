<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->string('link_url', 2048)->nullable();
            $table->string('link_label', 80)->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('push_requested_at')->nullable();
            $table->timestamp('push_sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_announcements');
    }
};
