<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationTable extends Migration
{
    /** @var \Illuminate\Database\Schema\Builder */
    protected $schema;

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
        $this->schema->create('reservation', function (Blueprint $table) {
            $table->foreignId('inventory_id')->nullable()->unique()->constrained();
            $table->foreignId('order_line_id')->nullable()->unique()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('reservation');
    }
}
