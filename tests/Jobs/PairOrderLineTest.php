<?php

namespace Just\Warehouse\Tests\Jobs;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Jobs\PairOrderLine;
use Illuminate\Contracts\Queue\ShouldQueue;

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
}
