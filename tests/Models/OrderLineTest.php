<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Models\Reservation;

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
    public function it_can_be_reserved()
    {
        $line = factory(OrderLine::class)->create(['id' => '1234']);
        $this->assertFalse($line->reservation->exists);

        $line->reserve();

        $this->assertTrue($line->fresh()->reservation->exists);
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

        $line->release();

        $this->assertFalse($line->fresh()->reservation->exists);
    }

    /** @test */
    public function it_has_a_reserved_inventory_item()
    {
        $line = factory(OrderLine::class)->create([
            'gtin' => '1300000000000',
        ]);

        $inventory = factory(Inventory::class)->create([
            'id' => '1234',
            'gtin' => '1300000000000',
        ]);

        factory(Reservation::class)->create([
            'inventory_id' => $inventory->id,
            'order_line_id' => $line->id,
        ]);

        tap($line->inventory, function ($inventory) {
            $this->assertInstanceOf(Inventory::class, $inventory);
            $this->assertEquals('1234', $inventory->id);
        });
    }
}
