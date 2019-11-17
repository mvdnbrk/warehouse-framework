<?php

namespace Just\Warehouse\Tests\Model\States\Order;

use Facades\OrderFactory;
use Just\Warehouse\Tests\TestCase;

class DetermineOrderStateTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_the_status_is_created()
    {
        $order = OrderFactory::create();

        $this->assertTrue($order->status->isCreated());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_backorder()
    {
        $order = OrderFactory::state('backorder')->create();

        $this->assertTrue($order->status->isBackorder());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_open()
    {
        $order = OrderFactory::state('open')->create();

        $this->assertTrue($order->status->isOpen());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_fulfilled()
    {
        $order = OrderFactory::state('fulfilled')->create();

        $this->assertTrue($order->status->isFulfilled());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_hold()
    {
        $order = OrderFactory::state('hold')->create();

        $this->assertTrue($order->status->isHold());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_deleted()
    {
        $order = OrderFactory::state('deleted')->create();

        $this->assertTrue($order->status->isDeleted());
    }
}
