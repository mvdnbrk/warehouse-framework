<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
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
     * The inventory associated with this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
