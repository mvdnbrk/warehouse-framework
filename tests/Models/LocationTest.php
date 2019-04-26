<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;

class LocationTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $location = factory(Location::class)->make();

        $this->assertEquals('warehouse', $location->getConnectionName());
    }

    /** @test */
    public function it_has_inventory()
    {
        $location = factory(Location::class)->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $location->inventory);
    }
}
