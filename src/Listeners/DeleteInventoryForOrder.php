<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Events\OrderFulfilled;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteInventoryForOrder implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\OrderFulfilled  $event
     * @return void
     */
    public function handle(OrderFulfilled $event)
    {
        $event->order->lines->each(function ($line) {
            $line->inventory->delete();
        });
    }
}
