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

    public function __construct(Order $order, string $originalStatus)
    {
        $this->order = $order;
        $this->originalStatus = $originalStatus;
    }
}
