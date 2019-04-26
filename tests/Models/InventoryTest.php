<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $inventory = factory(Inventory::class)->make();

        $this->assertEquals('warehouse', $inventory->getConnectionName());
    }

    /** @test */
    public function it_has_a_location()
    {
        $inventory = factory(Inventory::class)->create();

        $this->assertInstanceOf(Location::class, $inventory->location);
    }
}
