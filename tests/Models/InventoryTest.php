<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Models\Reservation;
use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;

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
    public function creating_inventory_without_a_gtin_throws_an_exception()
    {
        Event::fake(InventoryCreated::class);

        try {
            $inventory = factory(Inventory::class)->create([
                'gtin' => null,
            ]);
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
            $this->assertCount(0, Inventory::all());
            Event::assertNotDispatched(InventoryCreated::class);

            return;
        }

        $this->fail('Creating inventory without a GTIN succeeded.');
    }

    /** @test */
    public function it_can_be_reserved()
    {
        Event::fake();
        $inventory = factory(Inventory::class)->create(['id' => '1234']);

        $this->assertTrue($inventory->reserve());

        $this->assertCount(1, Reservation::all());
        tap(Reservation::first(), function ($reservation) {
            $this->assertEquals('1234', $reservation->inventory_id);
            $this->assertNull($reservation->order_line_id);
        });
    }

    /** @test */
    public function it_can_be_released()
    {
        Event::fake();
        $inventory = factory(Inventory::class)->create();
        $inventory->reserve();

        $this->assertEquals(1, $inventory->release());

        $this->assertCount(0, Reservation::all());
    }

    /** @test */
    public function it_can_determine_if_it_is_available()
    {
        $inventory = factory(Inventory::class)->create();
        $inventory->reserve();

        $this->assertFalse($inventory->isAvailable());

        $inventory->release();

        $this->assertTrue($inventory->fresh()->isAvailable());
    }

    /** @test */
    public function it_can_determine_if_it_is_reserved()
    {
        $inventory = factory(Inventory::class)->create();
        $inventory->reserve();

        $this->assertTrue($inventory->isReserved());

        $inventory->release();

        $this->assertFalse($inventory->fresh()->isReserved());
    }

    /** @test */
    public function it_dispatches_an_inventory_created_event_when_it_is_created()
    {
        Event::fake(InventoryCreated::class);
        $inventory = factory(Inventory::class)->create();

        Event::assertDispatched(InventoryCreated::class, function ($event) use ($inventory) {
            return $event->inventory->is($inventory);
        });
    }
}
