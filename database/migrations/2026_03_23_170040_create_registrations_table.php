<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // зв’язок з подією
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            // дані користувача
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();

            $table->timestamps();
        });
    }
};
