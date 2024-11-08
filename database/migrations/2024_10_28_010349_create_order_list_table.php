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
        Schema::create('order_list', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('event_id');
            $table->string('event_date', length: 23);
            $table->float('ticket_adult_price', 2);
            $table->integer('ticket_adult_quantity');
            $table->float('ticket_kid_price', 2);
            $table->integer('ticket_kid_quantity');
            $table->string('barcode', length: 120);
            $table->float('equal_price', 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_list');
    }
};
