<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_hash', 64);
            $table->date('visited_on');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['session_hash', 'visited_on']);
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
