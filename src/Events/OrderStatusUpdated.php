<?php

namespace Just\Warehouse\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Order;

class OrderStatusUpdated
{
    use Dispatchable,
        SerializesModels;

    /**
     * The order that updated it's status.
     *
     * @var \Just\Warehouse\Models\Order
     */
    public $order;

    /**
     * The original order status.
     *
     * @var string
     */
    public $originalStatus;

    /**
     * Create a new event instance.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @param  string  $originalStatus
     * @return void
     */
    public function __construct(Order $order, $originalStatus)
    {
        $this->order = $order;
        $this->originalStatus = $originalStatus;
    }
}
