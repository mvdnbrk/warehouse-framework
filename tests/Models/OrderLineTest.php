<?php

namespace Just\Warehouse\Tests\Model;

use LogicException;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Just\Warehouse\Models\Reservation;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;

class OrderLineTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $line = factory(OrderLine::class)->make(['order_id' => null]);

        $this->assertEquals('warehouse', $line->getConnectionName());
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $line = factory(OrderLine::class)->make();

        $this->assertInstanceOf(Order::class, $line->order);
    }

    /** @test */
    public function it_has_a_reservation()
    {
        $line = factory(OrderLine::class)->create();

        $this->assertInstanceOf(Reservation::class, $line->reservation);
    }

    /** @test */
    public function it_dispatches_an_inventory_created_event_when_it_is_created()
    {
        Event::fake(OrderLineCreated::class);
        $line = factory(OrderLine::class)->create();

        $this->assertCount(1, OrderLine::all());
        Event::assertDispatched(OrderLineCreated::class, function ($event) use ($line) {
            return $event->line->is($line);
        });
    }

    /** @test */
    public function creating_an_order_line_without_a_gtin_throws_an_exception()
    {
        try {
            $line = factory(OrderLine::class)->create([
                'gtin' => null,
            ]);
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
            $this->assertCount(0, OrderLine::all());

            return;
        }

        $this->fail('Creating an order line without a GTIN succeeded.');
    }

    /** @test */
    public function once_it_has_been_created_the_gtin_can_not_be_altered()
    {
        $line = factory(OrderLine::class)->create([
            'gtin' => '1300000000000',
        ]);

        try {
            $line->update([
                'gtin' => '14000000000003',
            ]);
        } catch (LogicException $e) {
            $this->assertEquals('The GTIN attribute can not be changed.', $e->getMessage());
            $this->assertEquals('1300000000000', $line->fresh()->gtin);

            return;
        }

        $this->fail('The GTIN attribute has changed.');
    }

    /** @test */
    public function once_it_has_been_created_the_order_id_can_not_be_altered()
    {
        $order = factory(Order::class)->create([
            'id' => 111,
        ]);
        $line = $order->addLine('1300000000000');

        try {
            $line->update([
                'order_id' => 999,
            ]);
        } catch (LogicException $e) {
            $this->assertEquals('The order ID attribute can not be changed.', $e->getMessage());
            $this->assertSame(111, $line->fresh()->order_id);

            return;
        }

        $this->fail('The order ID attribute has changed.');
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $line = factory(OrderLine::class)->create();
        $this->assertCount(1, Reservation::all());

        Queue::fake();
        $this->assertTrue($line->delete());

        $this->assertCount(0, Reservation::all());
        Queue::assertNotPushed(ReleaseOrderLine::class);
    }

    /** @test */
    public function it_can_be_reserved()
    {
        $line = factory(OrderLine::class)->create(['id' => '1234']);

        $this->assertTrue($line->reserve());

        $this->assertCount(1, Reservation::all());
        tap(Reservation::first(), function ($reservation) {
            $this->assertEquals('1234', $reservation->order_line_id);
            $this->assertNull($reservation->inventory_id);
        });
    }

    /** @test */
    public function it_can_be_released()
    {
        $line = factory(OrderLine::class)->create();
        $line->reserve();

        $this->assertEquals(1, $line->release());

        $this->assertCount(0, Reservation::all());
    }

    /** @test */
    public function it_can_determin_if_it_is_fulfilled()
    {
        Event::fake();
        $line = factory(OrderLine::class)->create([
            'gtin' => '1300000000000',
        ]);
        $inventory = factory(Inventory::class)->create([
            'id' => '1234',
            'gtin' => '1300000000000',
        ]);
        $this->assertFalse($line->isFulfilled());

        factory(Reservation::class)->create([
            'inventory_id' => $inventory->id,
            'order_line_id' => $line->id,
        ]);

        tap($line->fresh(), function ($line) {
            $this->assertTrue($line->isFulfilled());
            $this->assertInstanceOf(Inventory::class, $line->inventory);
            $this->assertEquals('1234', $line->inventory->id);
        });
    }
}
