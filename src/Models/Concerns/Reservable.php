<?php

namespace Just\Warehouse\Models\Concerns;

use Just\Warehouse\Models\Reservation;

/**
 * @property \Just\Warehouse\Models\Reservation $reservation
 */
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

    /**
     * Reserve the model.
     *
     * @return bool
     */
    public function reserve()
    {
        return $this->reservation->save();
    }

    /**
     * Release the model from being reserved.
     *
     * @return int
     */
    public function release()
    {
        return $this->reservation->delete();
    }

    /**
     * Determine if the model is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->reservation->exists === false;
    }

    /**
     * Determine if the model is reserved.
     *
     * @return bool
     */
    public function isReserved()
    {
        return $this->reservation->exists;
    }
}
