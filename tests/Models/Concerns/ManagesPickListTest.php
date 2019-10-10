<?php

namespace Just\Warehouse\Tests\Model\Concerns;

use Facades\LocationFactory;
use Facades\OrderFactory;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;

class ManagesPickListTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_picklist_is_available()
    {
        $order = OrderFactory::state('backorder')->withLines('1300000000000')->create();

        $this->assertFalse($order->hasPickList());

        LocationFactory::withInventory('1300000000000')->create();

        $this->assertTrue($order->fresh()->hasPickList());
    }

    /** @test */
    public function it_can_retrieve_a_picklist()
    {
        LocationFactory::withInventory('1300000000000')->create(['name' => 'Test Location']);
        OrderFactory::state('open')->withLines('1300000000000')->create();

        $picklist = Order::first()->pickList();

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
        OrderFactory::state('open')->withLines([
            '1300000000000',
            '1300000000000',
        ])->create();

        $picklist = Order::first()->pickList();

        $this->assertCount(1, $picklist);
        $this->assertSame(2, $picklist->first()->get('quantity'));
    }

    /** @test */
    public function it_is_sorted_by_location_name()
    {
        LocationFactory::withInventory('1300000000000')->create(['name' => 'Location B']);
        LocationFactory::withInventory('1300000000000')->create(['name' => 'Location A']);
        OrderFactory::state('open')->withLines([
            '1300000000000',
            '1300000000000',
        ])->create();

        $picklist = Order::first()->pickList();

        $this->assertCount(2, $picklist);
        $this->assertEquals('Location A', $picklist->first()->get('location'));
        $this->assertEquals('Location B', $picklist->last()->get('location'));
    }

    /** @test */
    public function it_returns_an_empty_collection_if_there_is_no_picklist_available()
    {
        OrderFactory::withLines(1)->create();

        $picklist = Order::first()->pickList();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $picklist);
        $this->assertTrue($picklist->isEmpty());
    }
}
