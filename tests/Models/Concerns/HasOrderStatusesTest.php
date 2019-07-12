<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Exceptions\InvalidStatusException;

class HasOrderStatusesTest extends TestCase
{
    /** @test */
    public function it_can_set_a_valid_status()
    {
        $order = factory(Order::class)->create();

        $order->status = 'open';

        $this->assertEquals('open', $order->status);
    }

    /** @test */
    public function it_can_determine_if_a_status_is_valid()
    {
        $order = factory(Order::class)->make();

        $this->assertTrue($order->isValidStatus('created'));
        $this->assertFalse($order->isValidStatus('invalid-status'));
    }

    /** @test */
    public function it_can_retrieve_the_status()
    {
        $order = factory(Order::class)->make();

        $this->assertEquals('draft', $order->status);

        $order->save();

        $this->assertEquals('created', $order->status);
    }

    /** @test */
    public function it_throws_an_exception_when_setting_an_invalid_status()
    {
        $order = factory(Order::class)->create();

        try {
            $order->status = 'invalid-status';
        } catch (InvalidStatusException $e) {
            $this->assertEquals("Invalid status 'invalid-status' for model [Just\Warehouse\Models\Order].", $e->getMessage());
            $this->assertEquals('Just\Warehouse\Models\Order', $e->getModel());
            $this->assertEquals('invalid-status', $e->getStatus());

            return;
        }

        $this->fail('Setting an invalid status succeeded.');
    }

    /** @test */
    public function it_throws_an_exception_when_updating_with_an_invalid_status()
    {
        $order = factory(Order::class)->create();

        try {
            $order->update(['status' => 'invalid-status']);
        } catch (InvalidStatusException $e) {
            $this->assertEquals("Invalid status 'invalid-status' for model [Just\Warehouse\Models\Order].", $e->getMessage());
            $this->assertEquals('created', $order->fresh()->status);

            return;
        }

        $this->fail('Updating the model with an invalid status succeeded.');
    }
}
