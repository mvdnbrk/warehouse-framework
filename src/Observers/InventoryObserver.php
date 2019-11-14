<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Jobs\PairInventory;
use Just\Warehouse\Models\Inventory;
use LogicException;

class InventoryObserver
{
    /**
     * Handle the Inventory "creating" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     *
     * @throws \InvalidGtinException
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
        $inventory->reserve();

        InventoryCreated::dispatch($inventory);
    }

    /**
     * Handle the Inventory "updating" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     *
     * @throws \LogicException
     */
    public function updating(Inventory $inventory)
    {
        if ($inventory->gtin !== $inventory->getOriginal('gtin')) {
            throw new LogicException('The GTIN attribute can not be changed.');
        }
    }

    /**
     * Handle the Inventory "restored" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     */
    public function restored(Inventory $inventory)
    {
        $inventory->reserve();

        PairInventory::dispatch($inventory);
    }
}
