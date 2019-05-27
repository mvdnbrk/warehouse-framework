<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Reservation;

class InventoryTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $inventory = factory(Inventory::class)->make(['location_id' => null]);

        $this->assertEquals('warehouse', $inventory->getConnectionName());
    }

    /** @test */
    public function it_has_a_location()
    {
        $inventory = factory(Inventory::class)->make();

        $this->assertInstanceOf(Location::class, $inventory->location);
    }

    /** @test */
    public function it_has_a_reservation()
    {
        $inventory = factory(Inventory::class)->create();

        $this->assertInstanceOf(Reservation::class, $inventory->reservation);
    }

    /** @test */
    public function it_can_be_reserved()
    {
        $inventory = factory(Inventory::class)->create(['id' => '1234']);
        $this->assertFalse($inventory->reservation->exists);

        $inventory->reserve();

        $this->assertTrue($inventory->fresh()->reservation->exists);
        $this->assertCount(1, Reservation::all());
        tap(Reservation::first(), function ($reservation) {
            $this->assertEquals('1234', $reservation->inventory_id);
            $this->assertNull($reservation->order_line_id);
        });
    }

    /** @test */
    public function it_can_be_released()
    {
        $inventory = factory(Inventory::class)->create();
        $inventory->reserve();

        $inventory->release();

        $this->assertFalse($inventory->fresh()->reservation->exists);
        $this->assertCount(0, Reservation::all());
    }
}
