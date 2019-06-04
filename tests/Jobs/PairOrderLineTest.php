<?php

namespace Just\Warehouse\Tests\Jobs;

use Carbon\Carbon;
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
            factory(OrderLine::class)->make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    /** @test */
    public function it_makes_a_reservation_when_inventory_is_not_available()
    {
        Event::fakeFor(function () {
            factory(OrderLine::class)->create();
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
        $inventory = factory(Inventory::class)->create([
            'gtin' => '1300000000000',
        ]);

        $line = factory(OrderLine::class)->create([
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
            factory(Inventory::class)->create([
                'gtin' => '1300000000000',
            ])->reserve();
        });

        $line = factory(OrderLine::class)->create([
            'gtin' => '1300000000000',
        ]);

        $this->assertCount(2, Reservation::all());
        $this->assertFalse($line->fresh()->isFulfilled());
    }

    /** @test */
    public function it_gets_fulfilled_with_the_oldest_inventory_item()
    {
        $inventory1 = factory(Inventory::class)->create([
            'id' => 1,
            'gtin' => '1300000000000',
        ]);

        Carbon::setTestNow(now()->subYear());
        $inventory2 = factory(Inventory::class)->create([
            'id' => 2,
            'gtin' => '1300000000000',
        ]);

        $line = factory(OrderLine::class)->create([
            'gtin' => '1300000000000',
        ]);

        $this->assertCount(1, Reservation::all());
        $this->assertTrue($line->fresh()->inventory->is($inventory2));
    }
}
