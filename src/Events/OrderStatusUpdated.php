<?php

namespace Just\Warehouse\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Order;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public Order $order;

    public string $originalStatus;

    public function __construct(Order $order, string $originalStatus)
    {
        $this->order = $order;
        $this->originalStatus = $originalStatus;
    }
}
