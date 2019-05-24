<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Reservation extends Pivot
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
}
