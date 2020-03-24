<?php

namespace Just\Warehouse\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;
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
     * It has an order through the orderline relation.
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    public function order()
    {
        return $this->hasOneDeepFromRelations(
                $this->orderline(),
                (new OrderLine)->order()
            );
    }

    /**
     * It has an order line through a reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function orderline()
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
     * Move the inventory model to another location.
     *
     * @param  \Just\Warehouse\Models\Location  $location
     * @return bool
     *
     * @throws \LogicException
     */
    public function move(Location $location)
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
