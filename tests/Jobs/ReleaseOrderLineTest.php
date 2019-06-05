<?php

namespace Just\Warehouse\Tests\Jobs;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Support\Facades\Queue;
use Just\Warehouse\Jobs\PairInventory;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReleaseOrderLineTest extends TestCase
{
    /** @test */
    public function it_implements_the_should_queue_contract()
    {
        $job = new ReleaseOrderLine(
            factory(OrderLine::class)->make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    /** @test */
    public function it_queues_a_job_to_pair_inventory_if_the_order_line_was_fulfilled()
    {
        $line = factory(OrderLine::class)->create([
            'gtin' => '1300000000000',
        ]);
        $inventory = factory(Inventory::class)->create([
            'gtin' => '1300000000000',
        ]);
        $this->assertTrue($line->isFulfilled());

        Queue::fake();
        $line->delete();

        Queue::assertPushed(PairInventory::class, function ($job) use ($inventory) {
            return $job->inventory->is($inventory);
        });
    }
}
