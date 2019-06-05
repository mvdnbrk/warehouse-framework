<?php

namespace Just\Warehouse\Models;

use LogicException;
use Just\Warehouse\Contracts\StorableEntity;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $gtin
 * @property int $location_id
 * @property \Just\Warehouse\Models\Location $location
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
 */
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

    /**
     * Move the inventory model to another location.
     *
     * @param  Location  $location
     * @return bool
     * @throws \LogicException
     */
    public function moveTo(Location $location)
    {
        if (! $location->exists) {
            throw new LogicException('Location does not exist.');
        }

        if ($this->location_id === $location->id) {
            throw new LogicException("Inventory can not be be moved to it's own location.");
        }

        return $this->update([
            'location_id' => $location->id,
        ]);
    }
}
