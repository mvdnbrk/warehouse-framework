<?php

namespace Just\Warehouse\Tests\Model;

use Facades\ReservationFactory;
use Just\Warehouse\Tests\TestCase;

class ReservationTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $reservation = ReservationFactory::make();

        $this->assertEquals('warehouse', $reservation->getConnectionName());
    }
}
