<?php

namespace Just\Warehouse\Tests\Database;

use Illuminate\Support\Facades\Schema;
use Just\Warehouse\Tests\TestCase;

class MigrationTest extends TestCase
{
    /** @test */
    public function it_runs_the_locations_migration()
    {
        $columns = Schema::connection('warehouse')->getColumnListing('locations');

        $this->assertEquals([
            'id',
            'gln',
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
            'created_at',
            'updated_at',
            'deleted_at',
        ], $columns);
    }

    /** @test */
    public function it_runs_the_orders_migration()
    {
        $columns = Schema::connection('warehouse')->getColumnListing('orders');

        $this->assertEquals([
            'id',
            'order_number',
            'meta',
            'status',
            'created_at',
            'updated_at',
            'deleted_at',
            'fulfilled_at',
        ], $columns);
    }

    /** @test */
    public function it_runs_the_order_lines_migration()
    {
        $columns = Schema::connection('warehouse')->getColumnListing('order_lines');

        $this->assertEquals([
            'id',
            'order_id',
            'gtin',
        ], $columns);
    }

    /** @test */
    public function it_runs_the_reservation_migration()
    {
        $columns = Schema::connection('warehouse')->getColumnListing('reservation');

        $this->assertEquals([
            'inventory_id',
            'order_line_id',
            'created_at',
            'updated_at',
        ], $columns);
    }
}
