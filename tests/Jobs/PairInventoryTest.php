<?php

namespace Just\Warehouse\Tests\Jobs;

use Facades\InventoryFactory;
use Facades\OrderLineFactory;
use Illuminate\Support\Carbon;
use Facades\ReservationFactory;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Jobs\PairInventory;
use Just\Warehouse\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Just\Warehouse\Events\InventoryCreated;

class PairInventoryTest extends TestCase
{
    /** @test */
    public function it_implements_the_should_queue_contract()
    {
        $job = new PairInventory(
            InventoryFactory::make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    /** @test */
    public function it_becomes_available_if_there_is_no_order_line_to_be_fulfilled()
    {
        Event::fakeFor(function () {
            InventoryFactory::create();
        }, [InventoryCreated::class]);

        tap(Inventory::first(), function ($inventory) {
            $this->assertFalse($inventory->isAvailable());

            PairInventory::dispatch($inventory);

            $this->assertCount(0, Reservation::all());
            $this->assertTrue($inventory->fresh()->isAvailable());
        });
    }

    /** @test */
    public function it_gets_reservered_for_an_order_line_that_needs_to_be_fulfilled()
    {
        OrderLineFactory::create([
            'id' => 1234,
            'gtin' => '1300000000000',
        ]);
        Event::fakeFor(function () {
            InventoryFactory::create([
                'id' => 5678,
                'gtin' => '1300000000000',
            ]);
        }, [InventoryCreated::class]);

        PairInventory::dispatch(Inventory::first());

        $this->assertCount(1, Reservation::all());
        tap(Inventory::first(), function ($inventory) {
            $this->assertEquals('5678', $inventory->reservation->inventory_id);
            $this->assertEquals('1234', $inventory->reservation->order_line_id);
        });
    }

    /** @test */
    public function it_gets_reserved_for_an_order_line_which_was_not_previously_fulfilled()
    {
        Event::fakeFor(function () {
            $fulfilledLine = OrderLineFactory::create([
                'id' => 1,
                'gtin' => '1300000000000',
            ]);
            $fulfilledInventory = InventoryFactory::create([
                'id' => 1,
                'gtin' => '1300000000000',
            ]);
            ReservationFactory::create([
                'inventory_id' => $fulfilledInventory->id,
                'order_line_id' => $fulfilledLine->id,
            ]);
        });

        tap(OrderLine::find(1), function ($line) {
            $this->assertTrue($line->isFulfilled());
            $this->assertSame(1, $line->inventory->id);
        });

        $line = OrderLineFactory::create([
            'id' => 1234,
            'gtin' => '1300000000000',
        ]);

        $inventory = InventoryFactory::create([
            'id' => 5678,
            'gtin' => '1300000000000',
        ]);

        tap(OrderLine::find(1234), function ($line) {
            $this->assertTrue($line->isFulfilled());
            $this->assertSame(5678, $line->inventory->id);
        });
        tap(OrderLine::find(1), function ($line) {
            $this->assertTrue($line->isFulfilled());
            $this->assertSame(1, $line->inventory->id);
        });
    }

    /** @test */
    public function it_gets_reserved_for_the_oldest_order_line_that_needs_to_be_fulfilled()
    {
        $line1 = OrderLineFactory::create([
            'id' => 1,
            'gtin' => '1300000000000',
        ]);

        Carbon::setTestNow(now()->subYear());
        $line2 = OrderLineFactory::create([
            'id' => 2,
            'gtin' => '1300000000000',
        ]);

        $inventory = InventoryFactory::create([
            'id' => 1,
            'gtin' => '1300000000000',
        ]);

        tap(OrderLine::find(2), function ($line) {
            $this->assertTrue($line->isFulfilled());
        });
        tap(OrderLine::find(1), function ($line) {
            $this->assertFalse($line->isFulfilled());
        });
    }
}
