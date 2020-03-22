<?php

namespace Just\Warehouse\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Just\Warehouse\Models\Reservation;

/**
 * @property array $with
 * @property \Just\Warehouse\Models\Reservation $reservation
 * @method \Illuminate\Database\Eloquent\Relations\HasOne hasOne($related, $foreignKey = null, $localKey = null)
 */
trait Reservable
{
    /**
     * It has a reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class)->withDefault();
    }

    /**
     * Reserve the model.
     *
     * @return bool
     */
    public function reserve(): bool
    {
        return $this->reservation->save();
    }

    /**
     * Release the model from being reserved.
     *
     * @return bool|null
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
    public function isAvailable(): bool
    {
        return $this->reservation->exists === false;
    }

    /**
     * Determine if the model is reserved.
     *
     * @return bool
     */
    public function isReserved(): bool
    {
        return $this->reservation->exists;
    }

    /**
     * Determine if the reservation is fulfilled.
     *
     * @return bool
     */
    public function isFulfilled()
    {
        return ! is_null($this->reservation->inventory_id)
            && ! is_null($this->reservation->order_line_id);
    }
}
