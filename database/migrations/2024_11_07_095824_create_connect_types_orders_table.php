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
        Schema::create('connect_types_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('order_list');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('ticket_types');

            $table->integer('count');

            $table->timestamps();
        });
    }
    // TODO добавить в таблицу заказов столбец булева который будет указывать есть ли в этой таблице упоминания о нем


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connect_types_orders');
    }
};
