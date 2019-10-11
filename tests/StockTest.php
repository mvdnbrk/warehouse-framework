<?php

namespace Just\Warehouse\Tests;

use Facades\OrderFactory;
use Facades\InventoryFactory;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Facades\Stock;
use Just\Warehouse\Tests\TestCase;

class StockTest extends TestCase
{
    /** @test */
    public function it_can_count_available_stock()
    {
        $this->assertSame(0, Stock::available());

        InventoryFactory::create();

        $this->assertSame(1, Stock::available());
    }

    /** @test */
    public function reserved_inventory_does_not_count_against_available_stock()
    {
        OrderFactory::state('open')->withLines(1)->create();

        $this->assertSame(0, Stock::available());
    }

    /** @test */
    public function it_can_count_available_stock_for_a_given_gtin()
    {
        $this->assertSame(0, Stock::available());

        InventoryFactory::create(['gtin' => '1300000000000']);
        InventoryFactory::create(['gtin' => '1400000000007']);

        $this->assertSame(1, Stock::gtin('1300000000000')->available());
    }

    /** @test */
    public function it_can_count_reserved_stock()
    {
        $this->assertSame(0, Stock::reserved());

        OrderFactory::state('open')->withLines(1)->create();

        $this->assertSame(1, Stock::reserved());
    }

    /** @test */
    public function it_can_count_reserved_stock_for_a_given_gtin()
    {
        $this->assertSame(0, Stock::gtin('1300000000000')->reserved());

        InventoryFactory::create(['gtin' => '1300000000000']);
        OrderFactory::state('open')->withLines('1300000000000')->create();

        $this->assertSame(1, Stock::gtin('1300000000000')->reserved());
    }

    /** @test */
    public function it_can_count_the_quantity_in_backorder()
    {
        $this->assertSame(0, Stock::backorder());

        OrderFactory::state('backorder')->withLines(1)->create();

        $this->assertSame(1, Stock::backorder());
    }

    /** @test */
    public function it_can_count_the_quantity_in_backorder_for_a_given_gtin()
    {
        $this->assertSame(0, Stock::gtin('1300000000000')->backorder());

        OrderFactory::state('backorder')->withLines('1300000000000')->create();

        $this->assertSame(0, Stock::gtin('1400000000007')->backorder());
        $this->assertSame(1, Stock::gtin('1300000000000')->backorder());
    }

    /** @test */
    public function setting_an_invalid_gtin_throws_an_exception()
    {
        try {
            Stock::gtin('invalid');
        } catch (InvalidGtinException $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());

            return;
        }

        $this->fail('Setting an invalid gtin succeeded.');
    }
}
