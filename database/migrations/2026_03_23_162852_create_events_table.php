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
    Schema::create('events', function (Blueprint $table) {
        $table->id();

        $table->string('title');
        $table->text('description');
        $table->dateTime('event_date');

        $table->string('image')->nullable();

        $table->string('status')->default('draft');

        $table->boolean('has_registration_button')->default(false);
        $table->string('registration_type')->default('none');
        $table->string('google_form_url')->nullable();

        $table->integer('max_participants')->nullable();

        $table->boolean('show_available_slots')->default(true);

        $table->timestamps();
    });
}
};
