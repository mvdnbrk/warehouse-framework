<?php

use Just\Warehouse\Models\Reservation;

class ReservationFactory
{
    public function create(array $overrides = [])
    {
        return factory(Reservation::class)->create($overrides);
    }

    public function make(array $overrides = [])
    {
        return factory(Reservation::class)->make($overrides);
    }
}
