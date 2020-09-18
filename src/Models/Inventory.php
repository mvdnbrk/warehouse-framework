<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int $id
 * @property string $gtin
 * @property int $location_id
 * @property \Just\Warehouse\Models\Location $location
 * @property \Just\Warehouse\Models\Order $order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
 */
class Inventory extends AbstractModel
{
    use Concerns\Reservable,
        HasRelationships,
        SoftDeletes;

    protected $casts = [
        'location_id' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function order(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->orderline(),
            (new OrderLine)->order()
        );
    }

    public function orderline(): HasOneThrough
    {
        return $this->hasOneThrough(
            OrderLine::class,
            Reservation::class,
            'inventory_id',
            'id',
            'id',
            'order_line_id'
        );
    }

    /**
     * @throws \LogicException
     */
    public function move(Location $location): bool
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
