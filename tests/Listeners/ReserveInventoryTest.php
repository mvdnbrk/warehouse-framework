<?php

namespace Just\Warehouse\Tests\Listeners;

use Just\Warehouse\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Just\Warehouse\Models\Inventory;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Listeners\CheckForUnfulfilledOrderLine;

class ReserveInventoryTest extends TestCase
{
    /** @test */
    public function it_gets_reserved_when_it_is_created()
    {
        Bus::fake();
        Event::fakeFor(function () {
            factory(Inventory::class)->create();
        });

        $inventory = Inventory::first();
        $this->assertFalse($inventory->isReserved());

        event(new InventoryCreated($inventory));

        $this->assertTrue($inventory->fresh()->isReserved());
    }
}
