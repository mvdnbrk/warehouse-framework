<?php

namespace Just\Warehouse\Tests\Database;

use Just\Warehouse\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    /** @test */
    public function it_runs_the_locations_migration()
    {
        $columns = Schema::connection('warehouse')->getColumnListing('locations');

        $this->assertEquals([
            'id',
            'name',
            'created_at',
            'updated_at',
        ], $columns);
    }

    /** @test */
    public function it_runs_the_inventories_migration()
    {
        $columns = Schema::connection('warehouse')->getColumnListing('inventories');

        $this->assertEquals([
            'id',
            'location_id',
            'gtin',
            'reserved_at',
            'created_at',
            'updated_at',
        ], $columns);
    }
}
