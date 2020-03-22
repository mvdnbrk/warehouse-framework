<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 36);
            $table->json('meta')->nullable();
            $table->string('status', 16)->default('created');
            $table->timestamps();
            $table->expires();
            $table->softDeletes();
            $table->timestamp('fulfilled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
