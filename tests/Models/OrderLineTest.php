<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\OrderLine;

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
        $line = factory(OrderLine::class)->create();

        $this->assertInstanceOf(Order::class, $line->order);
    }
}
