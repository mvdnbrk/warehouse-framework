<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation', function (Blueprint $table) {
            $table->unique(['inventory_id', 'order_line_id']);

            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('order_line_id')->nullable();
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
