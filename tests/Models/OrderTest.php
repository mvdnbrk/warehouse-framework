<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $order = factory(Order::class)->make();

        $this->assertEquals('warehouse', $order->getConnectionName());
    }

    /** @test */
    public function it_casts_the_meta_column_to_an_array()
    {
        $order = factory(Order::class)->make();

        $this->assertTrue($order->hasCast('meta', 'array'));
    }

    /** @test */
    public function it_has_order_lines()
    {
        $order = factory(Order::class)->make();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $order->lines);
    }
}
