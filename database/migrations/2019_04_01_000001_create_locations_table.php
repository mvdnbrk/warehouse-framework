<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
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
        $this->schema->create('locations', function (Blueprint $table) {
            $table->id();
            $table->gtin('gln', 13)->unique()->nullable();
            $table->string('name', 24)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('locations');
    }
}
