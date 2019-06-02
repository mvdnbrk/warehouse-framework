<?php

namespace Just\Warehouse\Models;

use Just\Warehouse\Contracts\StorableEntity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends AbstractModel implements StorableEntity
{
    use SoftDeletes,
        Concerns\Reservable;

    /**
     * It has a location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
