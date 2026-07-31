<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_game_scores', function (Blueprint $table) {
            $table->id();
            $table->string('nickname', 30);
            $table->unsignedSmallInteger('score');
            $table->timestamps();

            $table->index(['score', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_game_scores');
    }
};
