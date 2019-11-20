<?php

namespace Just\Warehouse\Tests\Model;

use Facades\InventoryFactory;
use Facades\LocationFactory;
use Facades\OrderFactory;
use Facades\OrderLineFactory;
use Facades\ReservationFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Events\OrderLineReplaced;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Models\Reservation;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Open;
use Just\Warehouse\Tests\TestCase;
use LogicException;

class OrderLineTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $line = OrderLineFactory::make();

        $this->assertEquals('warehouse', $line->getConnectionName());
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $line = OrderLineFactory::create();

        $this->assertInstanceOf(Order::class, $line->order);
    }

    /** @test */
    public function it_has_a_reservation()
    {
        $line = OrderLineFactory::create();

        $this->assertInstanceOf(Reservation::class, $line->reservation);
    }

    /** @test */
    public function it_has_a_location_through_the_inventory_relation()
    {
        $order = OrderFactory::withLines('1300000000000')->create();

        $this->assertNull(OrderLine::first()->location);

        $location = LocationFactory::withInventory('1300000000000')->create();

        $this->assertTrue(OrderLine::first()->location->is($location));
    }

    /** @test */
    public function it_dispatches_an_order_line_created_event_when_it_is_created()
    {
        Event::fake(OrderLineCreated::class);
        $line = OrderLineFactory::create();

        $this->assertCount(1, OrderLine::all());
        Event::assertDispatched(OrderLineCreated::class, function ($event) use ($line) {
            return $event->line->is($line);
        });
    }

    /** @test */
    public function creating_an_order_line_without_a_gtin_throws_an_exception()
    {
        try {
            OrderLineFactory::create([
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
    public function it_can_not_be_updated()
    {
        $line = OrderFactory::create(['id' => 111])->addLine('1300000000000');

        try {
            $line->update([
                'gtin' => '14000000000003',
                'order_id' => 999,
            ]);
        } catch (LogicException $e) {
            $this->assertEquals('An order line can not be updated.', $e->getMessage());
            tap($line->fresh(), function ($line) {
                $this->assertEquals('1300000000000', $line->gtin);
                $this->assertSame(111, $line->order_id);
            });

            return;
        }

        $this->fail('Updating an order line succeeded.');
    }

    /** @test */
    public function it_can_be_replaced_with_another_inventory_item()
    {
        Event::fake(OrderLineReplaced::class);
        $order = OrderFactory::create();
        $line = $order->addLine('1300000000000');
        $location = LocationFactory::create();
        $inventory1 = $location->addInventory('1300000000000');
        $inventory2 = $location->addInventory('1300000000000');

        $newLine = $line->replace();

        $this->assertFalse($newLine->is($line));
        $this->assertTrue($newLine->isFulfilled());
        tap($order->fresh(), function ($order) use ($inventory2) {
            $this->assertCount(1, $order->lines);
            $this->assertTrue($order->status->is(Created::class));
            $this->assertEquals('1300000000000', $order->lines->first()->gtin);
            $this->assertTrue($order->lines->first()->inventory->is($inventory2));
        });

        $this->assertCount(1, Inventory::all());
        tap($inventory1->fresh(), function ($inventory) {
            $this->assertTrue($inventory->trashed());
            $this->assertFalse($inventory->isReserved());
            $this->assertFalse($inventory->isFulfilled());
        });

        Event::assertDispatched(OrderLineReplaced::class, function ($event) use ($order, $inventory1, $newLine) {
            return $event->order->is($order)
                && $event->inventory->is($inventory1)
                && $event->line->is($newLine);
        });
    }

    /** @test */
    public function it_can_be_replaced_and_may_result_order_with_status_backorder()
    {
        $location = LocationFactory::create();
        $inventory = $location->addInventory('1300000000000');
        $order = OrderFactory::create();
        $line = $order->addLine('1300000000000');
        $order->process();

        $this->assertTrue($order->fresh()->status->is(Open::class));

        $newLine = $line->fresh()->replace();

        $this->assertCount(0, Inventory::all());
        tap($inventory->fresh(), function ($inventory) {
            $this->assertTrue($inventory->trashed());
            $this->assertFalse($inventory->isReserved());
            $this->assertFalse($inventory->isFulfilled());
        });

        $this->assertFalse($newLine->is($line));
        $this->assertFalse($newLine->isFulfilled());
        $this->assertTrue($order->fresh()->status->is(Backorder::class));
    }

    /** @test */
    public function trying_to_replace_an_order_line_which_is_not_fulfilled_throws_an_excpetion()
    {
        $line = OrderLineFactory::create();

        try {
            $this->assertFalse($line->replace());
        } catch (LogicException $e) {
            $this->assertEquals('This order line can not be replaced.', $e->getMessage());
            $this->assertTrue($line->isReserved());
            $this->assertFalse($line->isFulfilled());

            return;
        }

        $this->fail('Trying to replace an order line which is not fulfilled succeeded.');
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $line = OrderLineFactory::create();
        $this->assertCount(1, Reservation::all());

        Queue::fake();
        $this->assertTrue($line->delete());

        $this->assertCount(0, Reservation::all());
        Queue::assertNotPushed(ReleaseOrderLine::class);
    }

    /** @test */
    public function it_can_be_reserved()
    {
        $line = OrderLineFactory::create(['id' => '1234']);

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
        $line = OrderLineFactory::create();

        $line->reserve();

        $this->assertEquals(1, $line->release());
        $this->assertCount(0, Reservation::all());
    }

    /** @test */
    public function it_can_determine_if_it_is_fulfilled()
    {
        Event::fake();
        $line = OrderLineFactory::create([
            'gtin' => '1300000000000',
        ]);
        $inventory = InventoryFactory::create([
            'id' => '1234',
            'gtin' => '1300000000000',
        ]);
        $this->assertFalse($line->isFulfilled());

        ReservationFactory::create([
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
