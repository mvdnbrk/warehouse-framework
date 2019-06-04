<?php

namespace Just\Warehouse\Tests\Jobs;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Jobs\PairInventory;
use Illuminate\Contracts\Queue\ShouldQueue;

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
}
