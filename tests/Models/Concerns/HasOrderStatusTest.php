<?php

namespace Just\Warehouse\Tests\Model\Concerns;

use Facades\InventoryFactory;
use Facades\OrderFactory;
use Just\Warehouse\Exceptions\InvalidStatusException;
use Just\Warehouse\Tests\TestCase;

class HasOrderStatusTest extends TestCase
{
    /** @test */
    public function it_can_set_a_valid_status()
    {
        $order = OrderFactory::create();

        $order->status = 'open';

        $this->assertEquals('open', $order->status);
    }

    /** @test */
    public function it_can_determine_if_a_status_is_valid()
    {
        $order = OrderFactory::make();

        $this->assertTrue($order->isValidStatus('created'));
        $this->assertTrue($order->isValidStatus('open'));
        $this->assertTrue($order->isValidStatus('created'));
        $this->assertTrue($order->isValidStatus('deleted'));
        $this->assertTrue($order->isValidStatus('backorder'));
        $this->assertTrue($order->isValidStatus('fulfilled'));
        $this->assertFalse($order->isValidStatus('invalid-status'));
    }

    /** @test */
    public function it_can_retrieve_the_status()
    {
        $order = OrderFactory::make();

        $this->assertEquals('draft', $order->status);

        $order->save();

        $this->assertEquals('created', $order->status);
    }

    /** @test */
    public function it_throws_an_exception_when_setting_an_invalid_status()
    {
        $order = OrderFactory::create();

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
        $order = OrderFactory::create();

        try {
            $order->update(['status' => 'invalid-status']);
        } catch (InvalidStatusException $e) {
            $this->assertEquals("Invalid status 'invalid-status' for model [Just\Warehouse\Models\Order].", $e->getMessage());
            $this->assertEquals('created', $order->fresh()->status);

            return;
        }

        $this->fail('Updating the model with an invalid status succeeded.');
    }

    /** @test */
    public function it_can_determine_if_the_status_is_created()
    {
        $order = OrderFactory::create();

        $this->assertTrue($order->fresh()->isCreated());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_open()
    {
        $order = OrderFactory::state('backorder')->withLines('1300000000000')->create();
        $this->assertFalse($order->isOpen());

        InventoryFactory::create([
            'gtin' => '1300000000000',
        ]);

        $this->assertTrue($order->fresh()->isOpen());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_backorder()
    {
        $order = OrderFactory::withLines(1)->create();
        $this->assertFalse($order->isBackorder());

        $order->process();

        $this->assertTrue($order->fresh()->isBackorder());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_fulfilled()
    {
        $order = OrderFactory::state('open')->create();
        $this->assertFalse($order->isFulfilled());

        $order->markAsFulfilled();

        $this->assertTrue($order->fresh()->isFulfilled());
    }
}
