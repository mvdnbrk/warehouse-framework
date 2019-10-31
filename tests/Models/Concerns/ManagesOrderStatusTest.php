<?php

namespace Just\Warehouse\Tests\Model\Concerns;

use Facades\LocationFactory;
use Facades\OrderFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Fulfilled;
use Just\Warehouse\Models\States\Order\Open;
use Just\Warehouse\Tests\TestCase;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

class ManagesOrderStatusTest extends TestCase
{
    /** @test */
    public function it_can_be_processed()
    {
        $location = LocationFactory::withInventory('1300000000000')->create();
        $order = OrderFactory::withLines('1300000000000')->create();

        $this->assertTrue($order->fresh()->status->is(Created::class));

        $order->process();

        $this->assertTrue($order->fresh()->status->is(Open::class));
    }

    /** @test */
    public function it_can_be_processed_with_unfilfilled_order_lines_which_results_in_status_backorder()
    {
        $order = OrderFactory::withLines(1)->create();

        $order->process();

        $this->assertTrue($order->fresh()->status->is(Backorder::class));
    }

    /** @test */
    public function it_can_be_marked_as_fulfilled()
    {
        Carbon::setTestNow('2019-10-11 12:34:56');
        $order = OrderFactory::state('open')->withLines(1)->create();

        tap($order->fresh(), function ($order) {
            $order->markAsFulfilled();
            $this->assertTrue($order->status->is(Fulfilled::class));
            $this->assertEquals('2019-10-11 12:34:56', $order->fulfilled_at);
        });
        $this->assertTrue(Inventory::withTrashed()->first()->trashed());
    }

    /** @test */
    public function trying_to_mark_a_non_open_order_as_fulfilled_throws_an_exception()
    {
        Event::fake();
        $order = OrderFactory::create();

        try {
            $order->markAsFulfilled();
        } catch (TransitionNotFound $e) {
            $this->assertTrue($order->status->is(Created::class));
            $this->assertNull($order->fulfilled_at);
            Event::assertNotDispatched(OrderFulfilled::class);

            return;
        }

        $this->fail('Trying to mark a non open order as fulfilled succeeded.');
    }
}
