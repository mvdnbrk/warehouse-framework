<?php

namespace Just\Warehouse\Models;

use Just\Warehouse\Models\Reservation;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends AbstractModel
{
    use SoftDeletes;

    /**
     * It has a location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * It has a reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reservation()
    {
        return $this->hasOne(Reservation::class)->withDefault();
    }
}
