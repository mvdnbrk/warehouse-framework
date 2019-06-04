<?php

namespace Just\Warehouse\Tests\Model;

use LogicException;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;

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

    /** @test */
    public function it_can_not_be_deleted_if_it_has_inventory()
    {
        $location = factory(Location::class)->create();
        $location->addInventory('1300000000000');

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
        $location = factory(Location::class)->create();
        $inventory = $location->addInventory('1300000000000');
        $inventory->delete();

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
        $location = factory(Location::class)->create();

        $inventory = $location->addInventory('1300000000000');

        $this->assertCount(1, Inventory::all());
        $this->assertEquals($location->id, $inventory->location_id);
        $this->assertEquals('1300000000000', $inventory->gtin);
        Event::assertDispatched(InventoryCreated::class, function ($event) use ($inventory) {
            return $event->inventory->is($inventory);
        });
    }

    /** @test */
    public function adding_inventory_with_an_invalid_gtin_throws_an_exception()
    {
        Event::fake(InventoryCreated::class);
        $location = factory(Location::class)->create();

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
    public function it_can_remove_inventory()
    {
        $location = factory(Location::class)->create();
        $location->addInventory('1300000000000');
        $location->addInventory('1300000000000');

        $this->assertTrue($location->removeInventory('1300000000000'));

        $this->assertCount(1, Inventory::all());
    }

    /** @test */
    public function it_removes_the_oldest_inventory_first()
    {
        $location = factory(Location::class)->create();
        $inventory = $location->addInventory('1300000000000');
        factory(Inventory::class)->create([
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
        $location = factory(Location::class)->create();

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
        $location = factory(Location::class)->create();
        $location->addInventory('1300000000000');

        try {
            $location->removeInventory('1234560000005');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertCount(1, Inventory::all());

            return;
        }

        $this->fail('Removing inventory succeeded altough that inventory item does not exist.');
    }

    /** @test */
    public function it_can_remove_all_inventory()
    {
        $location = factory(Location::class)->create();
        $location->addInventory('1300000000000');
        $location->addInventory('1300000000000');

        $otherLocation = factory(Location::class)->create();
        $otherLocation->addInventory('1300000000000');
        $otherLocation->addInventory('1300000000000');

        $this->assertCount(4, Inventory::all());

        $this->assertEquals(2, $location->removeAllInventory());

        $this->assertCount(2, Inventory::all());
    }
}
