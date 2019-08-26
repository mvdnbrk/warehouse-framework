<?php

namespace Just\Warehouse\Tests\Model\Concerns;

use LogicException;
use Illuminate\Support\Carbon;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;

class ManagesOrderStatusTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_transition_is_valid()
    {
        $order = factory(Order::class)->make();

        $this->assertTrue($order->isValidTransition('created', 'open'));
        $this->assertTrue($order->isValidTransition('created', 'backorder'));
        $this->assertTrue($order->isValidTransition('backorder', 'open'));
        $this->assertTrue($order->isValidTransition('open', 'backorder'));
        $this->assertTrue($order->isValidTransition('open', 'fulfilled'));

        $this->assertFalse($order->isValidTransition('invalid', 'invalid'));
        $this->assertFalse($order->isValidTransition('fulfilled', 'backorder'));
        $this->assertFalse($order->isValidTransition('fulfilled', 'created'));
        $this->assertFalse($order->isValidTransition('fulfilled', 'open'));
    }

    /** @test */
    public function empty_values_for_transitions_will_return_false()
    {
        $order = factory(Order::class)->make();

        $this->assertFalse($order->isValidTransition('', 'created'));
        $this->assertFalse($order->isValidTransition('created', ''));
    }

    /** @test */
    public function it_can_be_processed()
    {
        $location = factory(Location::class)->create();
        $location->addInventory('1300000000000');
        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');

        $order->process();

        $this->assertEquals('open', $order->fresh()->status);
    }

    /** @test */
    public function it_can_be_processed_with_unfilfilled_order_lines_which_results_in_status_backorder()
    {
        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');

        $order->process();

        $this->assertEquals('backorder', $order->fresh()->status);
    }

    /** @test */
    public function it_can_be_marked_as_fulfilled()
    {
        Carbon::setTestNow('2019-10-11 12:34:56');
        $location = factory(Location::class)->create();
        $inventory = $location->addInventory('1300000000000');
        $order = factory(Order::class)->create();
        $order->addLine('1300000000000');
        $order->process();

        tap($order->fresh(), function ($order) {
            $order->markAsFulfilled();
            $this->assertEquals('fulfilled', $order->status);
            $this->assertEquals('2019-10-11 12:34:56', $order->fulfilled_at);
        });
        $this->assertTrue($inventory->fresh()->trashed());
    }

    /** @test */
    public function trying_to_mark_a_non_open_order_as_fulfilled_throws_an_exception()
    {
        $order = factory(Order::class)->create(['status' => 'created']);

        try {
            $order->markAsFulfilled();
        } catch (LogicException $e) {
            $this->assertEquals('This order can not be marked as fulfilled.', $e->getMessage());
            $this->assertEquals('created', $order->fresh()->status);

            return;
        }

        $this->fail('Trying to mark a non open order as fulfilled succeeded.');
    }

    /** @test */
    public function it_has_a_query_scope_for_orders_with_status_open()
    {
        factory(Order::class)->create(['status' => 'created']);
        $order = factory(Order::class)->create();
        $order->update(['status' => 'open']);

        $this->assertEquals(1, Order::open()->count());
    }

    /** @test */
    public function it_has_a_query_scope_for_orders_with_status_backorder()
    {
        factory(Order::class)->create(['status' => 'created']);
        $order = factory(Order::class)->create();
        $order->update(['status' => 'backorder']);

        $this->assertEquals(1, Order::backorder()->count());
    }
}
