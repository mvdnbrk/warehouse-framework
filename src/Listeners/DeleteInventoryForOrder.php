<?php

namespace Just\Warehouse\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Just\Warehouse\Events\OrderFulfilled;
use Just\Warehouse\Models\OrderLine;

class DeleteInventoryForOrder implements ShouldQueue
{
    public function handle(OrderFulfilled $event): void
    {
        $event->order->lines->each(function (OrderLine $line) {
            $line->inventory->delete();
        });
    }
}
