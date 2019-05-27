<?php

namespace Just\Warehouse\Models;

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
     * It beliongs to an order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

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
