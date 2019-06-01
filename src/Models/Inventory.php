<?php

namespace Just\Warehouse\Models;

use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Contracts\StorableEntity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends AbstractModel implements StorableEntity
{
    use SoftDeletes,
        Concerns\Reservable;

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => InventoryCreated::class,
    ];

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
