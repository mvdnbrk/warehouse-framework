<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Jobs\PairInventory;

class ScheduleInventoryPairing
{
    public function handle(InventoryCreated $event): void
    {
        PairInventory::dispatch($event->inventory);
    }
}
