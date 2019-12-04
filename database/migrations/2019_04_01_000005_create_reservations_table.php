<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')->nullable()->unique();
            $table->unsignedBigInteger('order_line_id')->nullable()->unique();
            $table->timestamps();

            $table->foreign('inventory_id')
                  ->references('id')
                  ->on('inventories');

            $table->foreign('order_line_id')
                  ->references('id')
                  ->on('order_lines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservation');
    }
}
