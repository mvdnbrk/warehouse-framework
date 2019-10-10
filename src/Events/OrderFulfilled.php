<?php

namespace Just\Warehouse\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Order;

class OrderFulfilled
{
    use Dispatchable,
        SerializesModels;

    /**
     * The order that was fulfilled.
     *
     * @var \Just\Warehouse\Models\Order
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
