<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Events\InventoryCreated;

class InventoryObserver
{
    /**
     * Handle the Inventory "created" event.
     *
     * @param  Just\Warehouse\Models\Inventory  $inventory
     * @return void
     */
    public function created(Inventory $inventory)
    {
        InventoryCreated::disptach($inventory);
    }
}
