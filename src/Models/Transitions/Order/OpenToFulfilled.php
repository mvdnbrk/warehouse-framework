<?php

namespace Just\Warehouse\Models\Transitions\Order;

use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Fulfilled;
use Spatie\ModelStates\Transition;

class OpenToFulfilled extends Transition
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): Order
    {
        $this->order->fulfilled_at = now();
        $this->order->status = new Fulfilled($this->order);

        $this->order->save();

        OrderFulfilled::dispatch($this->order);

        return $this->order;
    }
}
