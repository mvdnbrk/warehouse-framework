<?php

namespace Just\Warehouse\Models\Concerns;

use Just\Warehouse\Models\Reservation;

trait Reservable
{
    /**
     * It has a reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reservation()
    {
        return $this->hasOne(Reservation::class)->withDefault();
    }

    public function reserve()
    {
        $this->reservation->save();
    }

    public function release()
    {
        $this->reservation->delete();
    }
}
