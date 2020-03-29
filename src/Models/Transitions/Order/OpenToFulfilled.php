<?php

namespace Just\Warehouse\Models\Transitions\Order;

use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Fulfilled;
use Spatie\ModelStates\Transition;

class OpenToFulfilled extends Transition
{
    /**
     * @var \Just\Warehouse\Models\Order
     */

    /**
     * Create a transition instance.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    private Order $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Handle the transition.
     *
     * @return \Just\Warehouse\Models\Order
     */
    public function handle(): Order
    {
        $this->order->fulfilled_at = now();
        $this->order->status = new Fulfilled($this->order);

        $this->order->save();

        OrderFulfilled::dispatch($this->order);

        return $this->order;
    }
}
