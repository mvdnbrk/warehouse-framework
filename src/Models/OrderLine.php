<?php

namespace Just\Warehouse\Models;

use Just\Warehouse\Events\OrderLineCreated;

class OrderLine extends AbstractModel
{
    use Concerns\Reservable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => OrderLineCreated::class,
    ];

    /**
     * It beliongs to an order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
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
