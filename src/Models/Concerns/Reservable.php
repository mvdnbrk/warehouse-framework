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
    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class)->withDefault();
    }

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

    public function isAvailable(): bool
    {
        return $this->reservation->exists === false;
    }

    public function isReserved(): bool
    {
        return $this->reservation->exists;
    }

    public function isFulfilled(): bool
    {
        return ! is_null($this->reservation->inventory_id)
            && ! is_null($this->reservation->order_line_id);
    }
}
