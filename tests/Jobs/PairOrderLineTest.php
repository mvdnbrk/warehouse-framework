<?php

namespace Just\Warehouse\Tests\Jobs;

use Carbon\Carbon;
use Facades\InventoryFactory;
use Facades\OrderLineFactory;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Jobs\PairOrderLine;
use Just\Warehouse\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Just\Warehouse\Events\OrderLineCreated;

class PairOrderLineTest extends TestCase
{
    /** @test */
    public function it_implements_the_should_queue_contract()
    {
        $job = new PairOrderLine(
            OrderLineFactory::make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    /** @test */
    public function it_makes_a_reservation_when_inventory_is_not_available()
    {
        Event::fakeFor(function () {
            OrderLineFactory::create();
        });

        $line = OrderLine::first();
        $this->assertFalse($line->isReserved());
        $this->assertFalse($line->isFulfilled());

        event(new OrderLineCreated($line));

        tap($line->fresh(), function ($line) {
            $this->assertTrue($line->isReserved());
            $this->assertFalse($line->isFulfilled());
        });
    }

    /** @test */
    public function it_gets_fulfilled_when_inventory_is_available()
    {
        $inventory = InventoryFactory::create([
            'gtin' => '1300000000000',
        ]);

        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);

        $this->assertCount(1, Reservation::all());
        $this->assertTrue($line->fresh()->isFulfilled());
        $this->assertTrue($inventory->fresh()->isReserved());
    }

    /** @test */
    public function it_does_not_get_fulfilled_with_reserved_inventory()
    {
        Event::fakeFor(function () {
            InventoryFactory::create([
                'gtin' => '1300000000000',
            ])->reserve();
        });

        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);

        $this->assertCount(2, Reservation::all());
        $this->assertFalse($line->fresh()->isFulfilled());
    }

    /** @test */
    public function it_does_not_get_fulfilled_with_deleted_inventory()
    {
        Event::fakeFor(function () {
            InventoryFactory::create([
                'gtin' => '1300000000000',
            ])->delete();
        });

        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);

        $this->assertCount(1, Reservation::all());
        $this->assertFalse($line->fresh()->isFulfilled());
        $this->assertFalse(Inventory::withTrashed()->first()->isReserved());
    }

    /** @test */
    public function it_gets_fulfilled_with_the_oldest_inventory_item()
    {
        $inventory1 = InventoryFactory::create([
            'id' => 1,
            'gtin' => '1300000000000',
        ]);

        Carbon::setTestNow(now()->subYear());
        $inventory2 = InventoryFactory::create([
            'id' => 2,
            'gtin' => '1300000000000',
        ]);

        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);

        $this->assertCount(1, Reservation::all());
        $this->assertTrue($line->fresh()->inventory->is($inventory2));
    }
}
