<?php

namespace Just\Warehouse\Tests\Jobs;

use Illuminate\Support\Carbon;
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
            factory(Inventory::class)->make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    /** @test */
    public function it_becomes_available_if_there_is_no_order_line_to_be_fulfilled()
    {
        Event::fakeFor(function() {
            factory(Inventory::class)->create();
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
        factory(OrderLine::class)->create([
            'id' => 1234,
            'gtin' => '1300000000000',
        ]);
        Event::fakeFor(function() {
            factory(Inventory::class)->create([
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
            $fulfilledLine = factory(OrderLine::class)->create([
                'id' => 1,
                'gtin' => '1300000000000',
            ]);
            $fulfilledInventory = factory(Inventory::class)->create([
                'id' => 1,
                'gtin' => '1300000000000',
            ]);
            factory(Reservation::class)->create([
                'inventory_id' => $fulfilledInventory->id,
                'order_line_id' => $fulfilledInventory->id,
            ]);
        });

        tap(OrderLine::find(1), function ($line) {
            $this->assertTrue($line->isFulfilled());
            $this->assertSame(1, $line->inventory->id);
        });

        $line = factory(OrderLine::class)->create([
            'id' => 1234,
            'gtin' => '1300000000000',
        ]);

        $inventory = factory(Inventory::class)->create([
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
        $line1 = factory(OrderLine::class)->create([
            'id' => 1,
            'gtin' => '1300000000000',
        ]);

        Carbon::setTestNow(now()->subYear());
        $line2 = factory(OrderLine::class)->create([
            'id' => 2,
            'gtin' => '1300000000000',
        ]);

        $inventory = factory(Inventory::class)->create([
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
