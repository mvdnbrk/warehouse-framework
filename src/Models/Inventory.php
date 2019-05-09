<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends AbstractModel
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'reserved_at',
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

    /**
     * Release this item.
     *
     * @return bool
     */
    public function release()
    {
        return $this->update(['reserved_at' => null]);
    }

    /**
     * Reserve this item.
     *
     * @return bool
     */
    public function reserve()
    {
        return $this->update(['reserved_at' => now()]);
    }
}
