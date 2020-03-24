<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Jobs\PairOrderLine;

class ScheduleOrderLinePairing
{
    public function handle(OrderLineCreated $event): void
    {
        PairOrderLine::dispatch($event->line);
    }
}
