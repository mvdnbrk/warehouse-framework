<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Jobs\PairOrderLine;

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
