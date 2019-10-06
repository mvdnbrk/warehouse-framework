<?php

namespace Just\Warehouse\Tests\Model;

use LogicException;
use Facades\LocationFactory;
use Facades\InventoryFactory;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LocationTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $location = LocationFactory::make();

        $this->assertEquals('warehouse', $location->getConnectionName());
    }

    /** @test */
    public function it_has_inventory()
    {
        $location = LocationFactory::create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $location->inventory);
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $location = LocationFactory::create();

        $this->assertTrue($location->delete());

        $this->assertCount(0, Location::all());
    }

    /** @test */
    public function it_can_not_be_deleted_if_it_has_inventory()
    {
        $location = LocationFactory::withInventory(1)->create();

        try {
            $location->delete();
        } catch (LogicException $e) {
            $this->assertEquals('Location can not be deleted because it has inventory.', $e->getMessage());
            $this->assertCount(1, Location::all());

            return;
        }

        $this->fail('Location was deleted altough it has inventory items.');
    }

    /** @test */
    public function it_can_not_be_deleted_if_it_has_soft_deleted_inventory()
    {
        $location = LocationFactory::withInventory(1)->create();
        $location->fresh()->inventory->first->delete();
        $this->assertCount(0, $location->inventory);

        try {
            $location->delete();
        } catch (LogicException $e) {
            $this->assertEquals('Location can not be deleted because it has inventory.', $e->getMessage());
            $this->assertCount(1, Location::all());

            return;
        }

        $this->fail('Location was deleted altough it has inventory items.');
    }

    /** @test */
    public function it_can_add_inventory()
    {
        Event::fake(InventoryCreated::class);
        $location = LocationFactory::create();

        $inventory = $location->addInventory('1300000000000');

        $this->assertInstanceOf(Inventory::class, $inventory);
        $this->assertCount(1, Inventory::all());
        $this->assertEquals($location->id, $inventory->location_id);
        $this->assertEquals('1300000000000', $inventory->gtin);
        Event::assertDispatched(InventoryCreated::class, 1);
        Event::assertDispatched(InventoryCreated::class, function ($event) use ($inventory) {
            return $event->inventory->is($inventory);
        });
    }

    /** @test */
    public function it_can_add_multiple_inventory_items()
    {
        Event::fake(InventoryCreated::class);
        $location = LocationFactory::create();

        $inventories = $location->addInventory('1300000000000', 2);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $inventories);
        $this->assertSame(2, $inventories->count());
        $inventories->each(function ($inventory) use ($location) {
            $this->assertEquals($location->id, $inventory->location_id);
            $this->assertEquals('1300000000000', $inventory->gtin);
        });
        $this->assertCount(2, Inventory::all());
        Event::assertDispatched(InventoryCreated::class, 2);
    }

    /** @test */
    public function it_does_not_add_inventory_when_passing_amount_less_than_one()
    {
        Event::fake(InventoryCreated::class);
        $location = LocationFactory::create();

        $inventories = $location->addInventory('1300000000000', 0);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $inventories);
        $this->assertTrue($inventories->isEmpty());
        $this->assertCount(0, Inventory::all());
        Event::assertNotDispatched(InventoryCreated::class);
    }

    /** @test */
    public function adding_inventory_with_an_invalid_gtin_throws_an_exception()
    {
        Event::fake(InventoryCreated::class);
        $location = LocationFactory::create();

        try {
            $location->addInventory('invalid-gtin');
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
            $this->assertCount(0, Inventory::all());
            Event::assertNotDispatched(InventoryCreated::class);

            return;
        }

        $this->fail('Adding inventory succeeded with an invalid gtin.');
    }

    /** @test */
    public function it_can_move_inventory_to_another_location()
    {
        $location1 = LocationFactory::create();
        $inventory = $location1->addInventory('1300000000000');
        $location1->addInventory('1300000000000');
        $location2 = LocationFactory::create();
        $this->assertCount(2, $location1->inventory);

        $movedItem = $location1->move('1300000000000', $location2);

        $this->assertTrue($movedItem->is($inventory));
        $this->assertCount(1, $location1->fresh()->inventory);
        $this->assertCount(1, $location2->inventory);
    }

    /** @test */
    public function moving_inventory_with_an_invalid_gtin_throws_an_exception()
    {
        $location1 = LocationFactory::create();
        $location2 = LocationFactory::create();

        try {
            $location1->move('invalid-gtin', $location2);
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());

            return;
        }

        $this->fail('Moving inventory succeeded with an invalid gtin.');
    }

    /** @test */
    public function moving_inventory_to_a_location_that_does_not_exist_throws_an_exception()
    {
        $location1 = LocationFactory::withInventory('1300000000000')->create();
        $location2 = LocationFactory::make();
        $this->assertFalse($location2->exists);

        try {
            $location1->move('1300000000000', $location2);
        } catch (LogicException $e) {
            $this->assertEquals('Location does not exist.', $e->getMessage());

            return;
        }

        $this->fail('Moving inventory to a location that does not exist succeeded.');
    }

    /** @test */
    public function moving_inventory_to_its_own_location_throws_an_exception()
    {
        $location = LocationFactory::withInventory('1300000000000')->create();

        try {
            $location->move('1300000000000', $location);
        } catch (LogicException $e) {
            $this->assertEquals("Inventory can not be be moved to it's own location.", $e->getMessage());
            $this->assertCount(1, $location->inventory);

            return;
        }

        $this->fail("Trying to move inventory to it's own location succeeded.");
    }

    /** @test */
    public function moving_inventory_that_does_not_exist_throws_an_exception()
    {
        $location1 = LocationFactory::create();
        $location2 = LocationFactory::create();

        try {
            $location1->move('1300000000000', $location2);
        } catch (ModelNotFoundException $e) {
            $this->assertEquals('No query results for model [Just\Warehouse\Models\Inventory] 1300000000000', $e->getMessage());

            return;
        }

        $this->fail('Moving inventory succeeded with a gtin that does not exists on this location.');
    }

    /** @test */
    public function it_can_move_many_inventory_to_another_location()
    {
        $location1 = LocationFactory::create();
        $inventory1 = $location1->addInventory('1300000000000');
        $inventory2 = $location1->addInventory('1300000000000');
        $location1->addInventory('1300000000000');
        $location2 = LocationFactory::create();
        $this->assertCount(3, $location1->inventory);

        $models = $location1->moveMany([
            '1300000000000',
            '1300000000000',
        ], $location2);

        $this->assertCount(1, $location1->fresh()->inventory);
        $this->assertCount(2, $location2->inventory);
    }

    /** @test */
    public function moving_many_inventory_which_contains_invalid_data_should_not_be_processed()
    {
        $location1 = LocationFactory::create();
        $location1->addInventory('1300000000000');
        $location1->addInventory('1300000000000');
        $location2 = LocationFactory::create();
        $this->assertCount(2, $location1->inventory);

        try {
            $location1->moveMany([
                '1300000000000',
                '1300000000000',
                'invalid-gtin',
            ], $location2);
        } catch (InvalidGtinException $e) {
            $this->assertCount(2, $location1->fresh()->inventory);
            $this->assertCount(0, $location2->inventory);

            return;
        }

        $this->fail('Moving many inventory which contains invalid data succeeded.');
    }

    /** @test */
    public function it_can_remove_inventory()
    {
        $location = LocationFactory::withInventory(['1300000000000', '1300000000000'])->create();
        $this->assertCount(2, Inventory::all());

        $this->assertTrue($location->removeInventory('1300000000000'));

        $this->assertCount(1, Inventory::all());
    }

    /** @test */
    public function it_removes_the_oldest_inventory_item()
    {
        $location = LocationFactory::create();
        $inventory = $location->addInventory('1300000000000');
        InventoryFactory::create([
            'gtin' => '1300000000000',
            'created_at' => now()->subYear(),
            'location_id' => $location->id,
        ]);

        $location->removeInventory('1300000000000');

        $this->assertCount(1, Inventory::all());
        $this->assertTrue($inventory->is(Inventory::first()));
    }

    /** @test */
    public function reemoving_inventory_with_an_invalid_gtin_throws_an_exception()
    {
        $location = LocationFactory::create();

        try {
            $location->removeInventory('invalid-gtin');
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());

            return;
        }

        $this->fail('Removing inventory succeeded with an invalid gtin.');
    }

    /** @test */
    public function removing_inventory_that_does_not_exist_throws_an_exception()
    {
        $location = LocationFactory::withInventory('1300000000000')->create();

        try {
            $location->removeInventory('1234560000005');
        } catch (ModelNotFoundException $e) {
            $this->assertEquals('No query results for model [Just\Warehouse\Models\Inventory] 1234560000005', $e->getMessage());
            $this->assertCount(1, Inventory::all());

            return;
        }

        $this->fail('Removing inventory succeeded altough that inventory item does not exist.');
    }

    /** @test */
    public function it_can_remove_all_inventory()
    {
        $location = LocationFactory::withInventory(['1300000000000', '1300000000000'])->create();
        $otherLocation = LocationFactory::withInventory(['1300000000000', '1300000000000'])->create();

        $this->assertCount(4, Inventory::all());

        $this->assertEquals(2, $location->removeAllInventory());

        $this->assertCount(2, Inventory::all());
    }
}
