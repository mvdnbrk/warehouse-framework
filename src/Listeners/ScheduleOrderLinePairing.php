<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Jobs\PairOrderLine;
use Just\Warehouse\Events\OrderLineCreated;

class ScheduleOrderLinePairing
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\OrderLineCreated  $event
     * @return void
     */
    public function handle(OrderLineCreated $event)
    {
        PairOrderLine::dispatch($event->line);
    }
}
