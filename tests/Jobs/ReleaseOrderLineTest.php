<?php

namespace Just\Warehouse\Tests\Jobs;

use Facades\InventoryFactory;
use Facades\OrderLineFactory;
use Just\Warehouse\Tests\TestCase;
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
            OrderLineFactory::make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    /** @test */
    public function it_sets_the_number_of_times_the_job_may_be_attempted()
    {
        $job = new ReleaseOrderLine(
            OrderLineFactory::make()
        );

        $this->assertSame(1, $job->tries);
    }

    /** @test */
    public function it_queues_a_job_to_pair_inventory_if_the_order_line_was_fulfilled()
    {
        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);
        $inventory = InventoryFactory::create([
            'gtin' => '1300000000000',
        ]);
        $this->assertTrue($line->isFulfilled());

        Queue::fake();
        $line->delete();

        Queue::assertPushed(PairInventory::class, function (PairInventory $job) use ($inventory) {
            return $job->inventory->is($inventory);
        });
    }
}
