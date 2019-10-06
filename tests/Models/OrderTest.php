<?php

namespace Just\Warehouse\Tests\Model;

use LogicException;
use Facades\OrderFactory;
use Facades\LocationFactory;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Just\Warehouse\Jobs\PairOrderLine;
use Just\Warehouse\Models\Reservation;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Events\OrderStatusUpdated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Exceptions\InvalidOrderNumberException;

class OrderTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $order = OrderFactory::make();

        $this->assertEquals('warehouse', $order->getConnectionName());
    }

    /** @test */
    public function it_casts_the_meta_column_to_an_array()
    {
        $order = OrderFactory::make();

        $this->assertTrue($order->hasCast('meta', 'array'));
    }

    /** @test */
    public function it_has_order_lines()
    {
        $order = OrderFactory::make();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $order->lines);
    }

    /** @test */
    public function it_sets_status_to_created_when_trying_to_create_an_order_with_another_status_than_created()
    {
        $order = OrderFactory::create(['status' => 'open']);

        $this->assertEquals('created', $order->status);
    }

    /** @test */
    public function creating_an_order_without_an_order_number_throws_an_exception()
    {
        try {
            $order = OrderFactory::create(['order_number' => '']);
        } catch (InvalidOrderNumberException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
            $this->assertCount(0, Order::all());

            return;
        }

        $this->fail('Creating an order without an order number succeeded.');
    }

    /** @test */
    public function it_can_add_an_order_line()
    {
        Event::fake(OrderLineCreated::class);
        $order = OrderFactory::create();

        $line = $order->addLine('1300000000000');

        $this->assertInstanceOf(OrderLine::class, $line);
        $this->assertCount(1, OrderLine::all());
        $this->assertEquals($order->id, $line->order_id);
        $this->assertEquals('1300000000000', $line->gtin);
        Event::assertDispatched(OrderLineCreated::class, 1);
        Event::assertDispatched(OrderLineCreated::class, function ($event) use ($line) {
            return $event->line->is($line);
        });
    }

    /** @test */
    public function it_can_add_multiple_order_lines()
    {
        Event::fake(OrderLineCreated::class);
        $order = OrderFactory::create();

        $lines = $order->addLine('1300000000000', 2);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $lines);
        $this->assertSame(2, $lines->count());
        $lines->each(function ($line) use ($order) {
            $this->assertEquals($order->id, $line->order_id);
            $this->assertEquals('1300000000000', $line->gtin);
        });
        $this->assertCount(2, OrderLine::all());
        Event::assertDispatched(OrderLineCreated::class, 2);
    }

    /** @test */
    public function it_does_not_add_order_lines_when_passing_amount_less_than_one()
    {
        Event::fake(OrderLineCreated::class);
        $order = OrderFactory::create();

        $lines = $order->addLine('1300000000000', 0);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $lines);
        $this->assertTrue($lines->isEmpty());
        $this->assertCount(0, OrderLine::all());
        Event::assertNotDispatched(OrderLineCreated::class);
    }

    /** @test */
    public function adding_an_order_line_with_an_invalid_gtin_throws_an_exception()
    {
        $order = OrderFactory::create();

        try {
            $order->addLine('invalid-gtin');
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
            $this->assertCount(0, OrderLine::all());

            return;
        }

        $this->fail('Adding an order line succeeded with an invalid gtin.');
    }

    /** @test */
    public function it_dispatches_an_order_status_updated_event_when_the_status_has_changed()
    {
        Event::fake(OrderStatusUpdated::class);
        $order = OrderFactory::create();
        $order->addLine('1300000000000');
        $order->process();

        Event::assertDispatched(OrderStatusUpdated::class, 1);
        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
            return $event->order->is($order)
                && $event->originalStatus == 'created'
                && $event->order->status == 'backorder';
        });
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $order = OrderFactory::create();
        $line = $order->addLine('1300000000000');

        $this->assertTrue($order->delete());

        $this->assertCount(0, Order::all());
        $this->assertCount(0, Reservation::all());
        $this->assertCount(1, Order::withTrashed()->get());
        $this->assertEquals('deleted', $order->fresh()->status);
        tap($line->fresh(), function ($line) {
            $this->assertFalse($line->isReserved());
            $this->assertFalse($line->isFulfilled());
        });
    }

    /** @test */
    public function it_can_be_soft_deleted_and_dispatches_jobs_to_release_the_order_lines()
    {
        $order = OrderFactory::create();
        $line1 = $order->addLine('1300000000000');
        $line2 = $order->addLine('1300000000000');

        Queue::fake();
        $this->assertTrue($order->delete());

        Queue::assertPushed(ReleaseOrderLine::class, function ($job) use ($line1) {
            return $job->line->is($line1);
        });
        Queue::assertPushed(ReleaseOrderLine::class, function ($job) use ($line2) {
            return $job->line->is($line2);
        });
        $this->assertCount(0, Order::all());
        $this->assertCount(1, Order::withTrashed()->get());
    }

    /** @test */
    public function it_can_be_restored()
    {
        $order = OrderFactory::create();
        $line = $order->addLine('1300000000000');
        $order->delete();

        $this->assertTrue($order->restore());

        $this->assertEquals('created', $order->fresh()->status);
        $this->assertCount(1, Order::all());
        $this->assertCount(1, Reservation::all());
        $this->assertCount(0, Order::onlyTrashed()->get());
        tap($line->fresh(), function ($line) {
            $this->assertTrue($line->isReserved());
            $this->assertFalse($line->isFulfilled());
        });
    }

    /** @test */
    public function it_can_be_restored_and_dispatches_jobs_to_pair_the_order_lines()
    {
        $order = OrderFactory::create();
        $line1 = $order->addLine('1300000000000');
        $line2 = $order->addLine('1300000000000');
        $order->delete();

        Queue::fake();
        $this->assertTrue($order->restore());

        Queue::assertPushed(PairOrderLine::class, function ($job) use ($line1) {
            return $job->line->is($line1);
        });
        Queue::assertPushed(PairOrderLine::class, function ($job) use ($line2) {
            return $job->line->is($line2);
        });
        $this->assertCount(1, Order::all());
        $this->assertCount(0, Order::onlyTrashed()->get());
    }

    /** @test */
    public function it_can_not_be_force_deleted()
    {
        $order = OrderFactory::create();

        try {
            $order->forceDelete();
        } catch (LogicException $e) {
            $this->assertEquals('An order can not be force deleted.', $e->getMessage());
            $this->assertCount(1, Order::all());

            return;
        }

        $this->fail('Force deleting an order succceeded.');
    }

    /** @test */
    public function adding_inventory_does_not_change_the_status_from_created_to_open()
    {
        $order = OrderFactory::withLines('1300000000000')->create();

        $this->assertEquals('created', $order->status);

        LocationFactory::withInventory('1300000000000')->create();

        $this->assertEquals('created', $order->fresh()->status);
    }
}
