<?php

namespace Just\Warehouse\Tests\Model;

use Facades\LocationFactory;
use Facades\OrderFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Events\OrderStatusUpdated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Exceptions\InvalidOrderNumberException;
use Just\Warehouse\Jobs\PairOrderLine;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Models\Reservation;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Deleted;
use Just\Warehouse\Models\States\Order\Fulfilled;
use Just\Warehouse\Models\States\Order\Hold;
use Just\Warehouse\Models\States\Order\Open;
use Just\Warehouse\Tests\TestCase;
use LogicException;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

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

        $this->assertInstanceOf(Collection::class, $order->lines);
    }

    /** @test */
    public function it_sets_status_to_created_when_trying_to_create_an_order_with_another_status_than_created()
    {
        $order = OrderFactory::create(['status' => Open::class]);

        $this->assertTrue($order->status->is(Created::class));
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

        $this->assertInstanceOf(Collection::class, $lines);
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

        $this->assertInstanceOf(Collection::class, $lines);
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
                && $event->order->status->is(Backorder::class);
        });
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $order = OrderFactory::create([
            'expires_at' => 10,
        ]);
        $line = $order->addLine('1300000000000');
        $this->assertTrue($order->willExpire());

        $this->assertTrue($order->delete());

        $this->assertCount(0, Order::all());
        $this->assertCount(0, Reservation::all());
        tap($order->fresh(), function ($order) {
            $this->assertTrue($order->status->is(Deleted::class));
            $this->assertTrue($order->trashed());
            $this->assertFalse($order->willExpire());
        });
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

        $this->assertTrue($order->fresh()->status->is(Created::class));
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
            $this->assertTrue($order->fresh()->status->is(Created::class));
            $this->assertCount(1, Order::all());

            return;
        }

        $this->fail('Force deleting an order succceeded.');
    }

    /** @test */
    public function adding_inventory_does_not_change_the_status_from_created_to_open()
    {
        $order = OrderFactory::withLines('1300000000000')->create();

        $this->assertTrue($order->status->is(Created::class));

        LocationFactory::withInventory('1300000000000')->create();

        $this->assertTrue($order->fresh()->status->is(Created::class));
    }

    /** @test */
    public function it_can_be_processed()
    {
        $location = LocationFactory::withInventory('1300000000000')->create();
        $order = OrderFactory::withLines('1300000000000')->create([
            'expires_at' => 10,
        ]);

        tap($order->fresh(), function ($order) {
            $this->assertTrue($order->willExpire());
            $this->assertTrue($order->status->is(Created::class));
        });

        $order->process();

        tap($order->fresh(), function ($order) {
            $this->assertFalse($order->willExpire());
            $this->assertTrue($order->status->is(Open::class));
        });
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

    /** @test */
    public function it_can_determine_if_a_picklist_is_available()
    {
        $order = OrderFactory::state('backorder')->withLines('1300000000000')->create();

        $this->assertFalse($order->hasPickList());

        LocationFactory::withInventory('1300000000000')->create();

        $this->assertTrue($order->fresh()->hasPickList());
    }

    /** @test */
    public function it_can_retrieve_a_picklist()
    {
        LocationFactory::withInventory('1300000000000')->create(['name' => 'Test Location']);
        OrderFactory::state('open')->withLines('1300000000000')->create();

        $picklist = Order::first()->pickList();

        $this->assertInstanceOf(Collection::class, $picklist);
        $this->assertInstanceOf(Collection::class, $picklist->first());

        $this->assertCount(1, $picklist);
        $this->assertEquals('1300000000000', $picklist->first()->get('gtin'));
        $this->assertEquals('Test Location', $picklist->first()->get('location'));
        $this->assertSame(1, $picklist->first()->get('quantity'));
    }

    /** @test */
    public function a_picklist_has_the_correct_quantity()
    {
        OrderFactory::state('open')->withLines([
            '1300000000000',
            '1300000000000',
        ])->create();

        $picklist = Order::first()->pickList();

        $this->assertCount(1, $picklist);
        $this->assertSame(2, $picklist->first()->get('quantity'));
    }

    /** @test */
    public function a_picklist_is_sorted_by_location_name()
    {
        LocationFactory::withInventory('1300000000000')->create(['name' => 'Location B']);
        LocationFactory::withInventory('1300000000000')->create(['name' => 'Location A']);
        OrderFactory::state('open')->withLines([
            '1300000000000',
            '1300000000000',
        ])->create();

        $picklist = Order::first()->pickList();

        $this->assertCount(2, $picklist);
        $this->assertEquals('Location A', $picklist->first()->get('location'));
        $this->assertEquals('Location B', $picklist->last()->get('location'));
    }

    /** @test */
    public function it_returns_an_empty_collection_if_there_is_no_picklist_available()
    {
        OrderFactory::withLines(1)->create();

        $picklist = Order::first()->pickList();

        $this->assertInstanceOf(Collection::class, $picklist);
        $this->assertTrue($picklist->isEmpty());
    }

    /** @test */
    public function it_can_be_put_on_hold()
    {
        $order = OrderFactory::withLines(1)->create();

        $this->assertTrue($order->hold());
        $this->assertTrue($order->status->is(Hold::class));
    }

    /** @test */
    public function an_open_order_can_be_put_on_hold()
    {
        $order = OrderFactory::state('open')->create();

        $this->assertTrue($order->hold());
        $this->assertTrue($order->status->is(Hold::class));
    }

    /** @test */
    public function a_backorder_can_be_put_on_hold()
    {
        $order = OrderFactory::state('backorder')->create();

        $this->assertTrue($order->hold());
        $this->assertTrue($order->status->is(Hold::class));
    }

    /** @test */
    public function an_order_without_order_lines_can_not_be_put_on_hold()
    {
        $order = OrderFactory::create();

        $this->assertFalse($order->hold());
        $this->assertTrue($order->fresh()->status->is(Created::class));
    }

    /** @test */
    public function it_can_be_unholded()
    {
        $order = OrderFactory::state('open')->create();
        $order->hold();

        $this->assertTrue($order->unhold());
        $this->assertTrue($order->fresh()->status->is(Open::class));
    }

    /** @test */
    public function a_deleted_order_can_not_be_unholded()
    {
        $order = OrderFactory::create();
        $order->delete();

        $this->assertFalse($order->unhold());
        $this->assertTrue($order->fresh()->status->is(Deleted::class));
    }

    /** @test */
    public function a_backorder_which_is_unholded_returns_to_status_backorder()
    {
        $order = OrderFactory::state('backorder')->create();
        $order->hold();

        $this->assertTrue($order->unhold());
        $this->assertTrue($order->fresh()->status->is(Backorder::class));
    }

    /** @test */
    public function it_can_determine_if_the_status_is_created()
    {
        $order = OrderFactory::create();

        $this->assertTrue($order->isCreated());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_backorder()
    {
        $order = OrderFactory::state('backorder')->create();

        $this->assertTrue($order->isBackorder());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_open()
    {
        $order = OrderFactory::state('open')->create();

        $this->assertTrue($order->isOpen());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_fulfilled()
    {
        $order = OrderFactory::state('fulfilled')->create();

        $this->assertTrue($order->isFulfilled());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_hold()
    {
        $order = OrderFactory::state('hold')->create();

        $this->assertTrue($order->isHold());
    }

    /** @test */
    public function it_can_determine_if_the_status_is_deleted()
    {
        $order = OrderFactory::state('deleted')->create();

        $this->assertTrue($order->isDeleted());
    }
}
