<?php

namespace Just\Warehouse\Models;

use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int $id
 * @property int $order_id
 * @property string $gtin
 * @property \Just\Warehouse\Models\Order $order
 * @property \Just\Warehouse\Models\Inventory $inventory
 */
class OrderLine extends AbstractModel
{
    use HasRelationships,
        Concerns\Reservable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'order_id' => 'integer',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'location',
    ];

    /**
     * It belongs to an order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * It has a location through the inventory relation.
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    public function location()
    {
        return $this->hasOneDeepFromRelations(
                $this->inventory(),
                (new Inventory)->location()
            )
            ->withTrashed('inventories.deleted_at');
    }

    /**
     * It has an inventory item through a reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function inventory()
    {
        return $this->hasOneThrough(
            Inventory::class,
            Reservation::class,
            'order_line_id',
            'id',
            'id',
            'inventory_id'
        );
    }
}
