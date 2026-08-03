<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->text('summary')->nullable();

            $table->string('status')->default('draft');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });

        Schema::create('event_summary_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_summary_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });

        Schema::create('event_summary_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_summary_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action');
            $table->text('description')->nullable();
            $table->json('changes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_summary_histories');
        Schema::dropIfExists('event_summary_images');
        Schema::dropIfExists('event_summaries');
    }
};
