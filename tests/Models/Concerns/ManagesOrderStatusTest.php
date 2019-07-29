<?php

namespace Just\Warehouse\Tests\Model\Concerns;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;

class ManagesOrderStatusTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_transition_is_valid()
    {
        $order = factory(Order::class)->make();

        $this->assertTrue($order->isValidTransition('created', 'open'));
        $this->assertTrue($order->isValidTransition('created', 'backorder'));
        $this->assertTrue($order->isValidTransition('backorder', 'open'));

        $this->assertFalse($order->isValidTransition('invalid', 'invalid'));
    }

    /** @test */
    public function empty_values_for_transitions_will_return_false()
    {
        $order = factory(Order::class)->make();

        $this->assertFalse($order->isValidTransition('', 'created'));
        $this->assertFalse($order->isValidTransition('created', ''));
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
