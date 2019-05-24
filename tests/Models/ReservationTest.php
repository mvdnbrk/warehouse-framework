<?php

namespace Just\Warehouse\Tests\Model;

use Just\Warehouse\Tests\TestCase;
use Just\Warehouse\Models\Reservation;

class ReservationTest extends TestCase
{
    /** @test */
    public function it_uses_the_warehouse_database_connection()
    {
        $reservation = factory(Reservation::class)->make();

        $this->assertEquals('warehouse', $reservation->getConnectionName());
    }
}
