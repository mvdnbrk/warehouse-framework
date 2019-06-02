<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Exceptions\InvalidOrderNumberException;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function creating(Order $order)
    {
        if (is_null($order->order_number)) {
            throw new InvalidOrderNumberException;
        }
    }
}
