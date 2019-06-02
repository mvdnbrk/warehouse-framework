<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;

class InventoryObserver
{
    /**
     * Handle the Inventory "creating" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     */
    public function creating(Inventory $inventory)
    {
        if (! is_gtin($inventory->gtin)) {
            throw new InvalidGtinException;
        }
    }

    /**
     * Handle the Inventory "created" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     */
    public function created(Inventory $inventory)
    {
        InventoryCreated::dispatch($inventory);
    }
}
