<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Jobs\PairInventory;

class ScheduleInventoryPairing
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\InventoryCreated  $event
     * @return void
     */
    public function handle(InventoryCreated $event)
    {
        PairInventory::dispatch($event->inventory);
    }
}
