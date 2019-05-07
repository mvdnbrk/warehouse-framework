<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
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
}
