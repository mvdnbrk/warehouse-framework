<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Reservation extends Pivot
{
     /**
     * The name of the foreign key column.
     *
     * @var string
     */
    protected $foreignKey = 'inventory_id';

     /**
     * The name of the "other key" column.
     *
     * @var string
     */
    protected $relatedKey = 'order_line_id';

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
