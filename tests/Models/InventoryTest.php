<?php

namespace Just\Warehouse\Tests\Model;

use LogicException;
use Facades\LocationFactory;
use Facades\InventoryFactory;
use Facades\OrderLineFactory;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Models\Reservation;
use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;

class InventoryTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $inventory = InventoryFactory::make();

        $this->assertEquals('warehouse', $inventory->getConnectionName());
    }

    /** @test */
    public function it_has_a_location()
    {
        $inventory = InventoryFactory::create();

        $this->assertInstanceOf(Location::class, $inventory->location);
    }

    /** @test */
    public function it_has_a_reservation()
    {
        $inventory = InventoryFactory::create();

        $this->assertInstanceOf(Reservation::class, $inventory->reservation);
    }

    /** @test */
    public function it_dispatches_an_inventory_created_event_when_it_is_created()
    {
        Event::fake(InventoryCreated::class);
        $inventory = InventoryFactory::create();

        Event::assertDispatched(InventoryCreated::class, function ($event) use ($inventory) {
            return $event->inventory->is($inventory);
        });
    }

    /** @test */
    public function creating_inventory_without_a_gtin_throws_an_exception()
    {
        Event::fake(InventoryCreated::class);

        try {
            InventoryFactory::create([
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
    public function once_it_has_been_created_the_gtin_can_not_be_altered()
    {
        $inventory = InventoryFactory::create([
            'gtin' => '1300000000000',
        ]);

        try {
            $inventory->update([
                'gtin' => '14000000000003',
            ]);
        } catch (LogicException $e) {
            $this->assertEquals('The GTIN attribute can not be changed.', $e->getMessage());
            $this->assertEquals('1300000000000', $inventory->fresh()->gtin);

            return;
        }

        $this->fail('The GTIN attribute has changed.');
    }

    /** @test */
    public function it_can_be_reserved()
    {
        Event::fake();
        $inventory = InventoryFactory::create(['id' => '1234']);

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
        $inventory = InventoryFactory::create();
        $inventory->reserve();

        $this->assertEquals(1, $inventory->release());

        $this->assertCount(0, Reservation::all());
    }

    /** @test */
    public function it_can_determine_if_it_is_available()
    {
        $inventory = InventoryFactory::create();
        $inventory->reserve();

        $this->assertFalse($inventory->isAvailable());

        $inventory->release();

        $this->assertTrue($inventory->fresh()->isAvailable());
    }

    /** @test */
    public function it_can_determine_if_it_is_reserved()
    {
        $inventory = InventoryFactory::create();
        $inventory->reserve();

        $this->assertTrue($inventory->isReserved());

        $inventory->release();

        $this->assertFalse($inventory->fresh()->isReserved());
    }

    /** @test */
    public function it_can_be_moved_to_another_location()
    {
        $location1 = LocationFactory::create();
        $inventory = $location1->addInventory('1300000000000');
        $location2 = LocationFactory::create();

        $this->assertTrue($inventory->move($location2));

        $this->assertCount(1, Inventory::all());
        $this->assertCount(0, $location1->fresh()->inventory);
        tap($location2->fresh(), function ($location2) use ($inventory) {
            $this->assertCount(1, $location2->inventory);
            $this->assertTrue($inventory->is($location2->inventory->first()));
        });
    }

    /** @test */
    public function it_can_not_be_moved_to_its_own_location()
    {
        $location = LocationFactory::create();
        $inventory = $location->addInventory('1300000000000');

        try {
            $inventory->move($location);
        } catch (LogicException $e) {
            $this->assertSame("Inventory can not be be moved to it's own location.", $e->getMessage());
            $this->assertCount(1, $location->fresh()->inventory);

            return;
        }

        $this->fail("Trying to move inventory to it's own location succeeded.");
    }

    /** @test */
    public function it_can_not_be_moved_to_a_location_that_does_not_exist()
    {
        $location1 = LocationFactory::create();
        $inventory = $location1->addInventory('1300000000000');
        $location2 = LocationFactory::make();
        $this->assertFalse($location2->exists);

        try {
            $inventory->move($location2);
        } catch (LogicException $e) {
            $this->assertSame('Location does not exist.', $e->getMessage());
            $this->assertCount(1, $location1->fresh()->inventory);

            return;
        }

        $this->fail('Trying to move inventory to a location that does not exist succeeded.');
    }

    /** @test */
    public function restoring_a_deleted_inventory_item_will_pair_with_an_order_line()
    {
        $inventory = InventoryFactory::create([
            'gtin' => '1300000000000',
        ]);
        $inventory->delete();
        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);

        $inventory->restore();

        tap($line->fresh(), function ($line) use ($inventory) {
            $this->assertTrue($line->isFulfilled());
            $this->assertTrue($line->inventory->is($inventory));
        });
    }
}
