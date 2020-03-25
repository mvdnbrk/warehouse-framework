<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    protected Builder $schema;

    public function __construct()
    {
        $this->schema = Schema::connection($this->getConnection());
    }

    public function getConnection(): ?string
    {
        return config('warehouse.database_connection');
    }

    public function up(): void
    {
        $this->schema->create('orders', function (Blueprint $table) {
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
        $this->schema->dropIfExists('orders');
    }
}
