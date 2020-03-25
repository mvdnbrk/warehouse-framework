<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class CreateOrderLinesTable extends Migration
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
        $this->schema->create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->gtin();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('order_lines');
    }
}
