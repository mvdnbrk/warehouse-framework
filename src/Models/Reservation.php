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
    protected $casts = [
        'inventory_id' => 'integer',
        'order_line_id' => 'integer',
    ];

    protected $foreignKey = 'inventory_id';

    protected $relatedKey = 'order_line_id';

    public function getConnectionName(): ?string
    {
        return config('warehouse.database_connection');
    }
}
