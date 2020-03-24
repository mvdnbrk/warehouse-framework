<?php

namespace Just\Warehouse\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Models\OrderLine;

class DeleteInventoryForOrder implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\OrderFulfilled  $event
     * @return void
     */
    public function handle(OrderFulfilled $event): void
    {
        $event->order->lines->each(function (OrderLine $line) {
            $line->inventory->delete();
        });
    }
}
