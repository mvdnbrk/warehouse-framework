<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;

class InventoryTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $inventory = factory(Inventory::class)->make(['location_id' => null]);

        $this->assertEquals('warehouse', $inventory->getConnectionName());
    }

    /** @test */
    public function it_mutates_the_reserved_at_column_to_a_date()
    {
        $inventory = factory(Inventory::class)->make(['location_id' => null]);

        $this->assertTrue(
            in_array('reserved_at', $inventory->getDates())
        );
    }

    /** @test */
    public function it_has_a_location()
    {
        $inventory = factory(Inventory::class)->create();

        $this->assertInstanceOf(Location::class, $inventory->location);
    }

    /** @test */
    public function it_can_be_reserved()
    {
        $inventory = factory(Inventory::class)->create();
        $this->assertNull($inventory->reserved_at);

        $this->assertTrue($inventory->reserve());

        $this->assertNotNull($inventory->fresh()->reserved_at);
    }

    /** @test */
    public function it_can_be_released()
    {
        $inventory = factory(Inventory::class)->states('reserved')->create();
        $this->assertNotNull($inventory->reserved_at);

        $this->assertTrue($inventory->release());

        $this->assertNull($inventory->fresh()->reserved_at);
    }
}
