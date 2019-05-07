<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'reserved_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('warehouse.database_connection');
    }

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
