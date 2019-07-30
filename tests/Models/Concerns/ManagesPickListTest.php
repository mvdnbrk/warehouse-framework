<?php

namespace Just\Warehouse\Tests\Model\Concerns;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;

class ManagesPickListTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_picklist_is_available()
    {
        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');
        $order->process();

        $this->assertFalse($order->fresh()->hasPickList());

        $location = factory(Location::class)->create(['name' => 'Test Location']);
        $location->addInventory('1300000000000');

        $this->assertTrue($order->fresh()->hasPickList());
    }

    /** @test */
    public function it_can_retrieve_a_picklist()
    {
        $location = factory(Location::class)->create(['name' => 'Test Location']);
        $location->addInventory('1300000000000');

        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');
        $order->process();

        $picklist = Order::find($order->id)->pickList();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $picklist);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $picklist->first());

        $this->assertCount(1, $picklist);
        $this->assertEquals('1300000000000', $picklist->first()->get('gtin'));
        $this->assertEquals('Test Location', $picklist->first()->get('location'));
        $this->assertSame(1, $picklist->first()->get('quantity'));
    }

    /** @test */
    public function it_has_the_correct_quantity()
    {
        $location = factory(Location::class)->create(['name' => 'Test Location']);
        $location->addInventory('1300000000000');
        $location->addInventory('1300000000000');

        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');
        $order->addLine('1300000000000');
        $order->process();

        $picklist = Order::find($order->id)->pickList();

        $this->assertCount(1, $picklist);
        $this->assertSame(2, $picklist->first()->get('quantity'));
    }

    /** @test */
    public function it_is_sorted_by_location_name()
    {
        $location1 = factory(Location::class)->create(['name' => 'Location B']);
        $location2 = factory(Location::class)->create(['name' => 'Location A']);
        $location1->addInventory('1300000000000');
        $location2->addInventory('1300000000000');

        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');
        $order->addLine('1300000000000');
        $order->process();

        $picklist = Order::find($order->id)->pickList();

        $this->assertCount(2, $picklist);
        $this->assertEquals('Location A', $picklist->first()->get('location'));
        $this->assertEquals('Location B', $picklist->last()->get('location'));
    }

    /** @test */
    public function it_returns_an_empty_collection_if_there_is_no_picklist_available()
    {
        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');
        $order->process();

        $picklist = Order::find($order->id)->pickList();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $picklist);
        $this->assertTrue($picklist->isEmpty());
    }
}
