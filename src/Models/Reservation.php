<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $inventory_id
 * @property int $order_line_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Reservation extends Pivot
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'inventory_id' => 'integer',
        'order_line_id' => 'integer',
    ];

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
    public function getConnectionName(): ?string
    {
        return config('warehouse.database_connection');
    }
}
