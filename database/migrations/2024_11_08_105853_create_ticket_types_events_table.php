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
        Schema::create('ticket_types_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_type_id');
            $table->unsignedBigInteger('event_id');
            $table->timestamps();
            $table->foreign('ticket_type_id')->references('id')->on('ticket_types');
            $table->foreign('event_id')->references('id')->on('events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types_events');
    }
};
