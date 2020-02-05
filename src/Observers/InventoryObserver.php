<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Events\InventoryCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Jobs\PairInventory;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\States\Order\Created;
use LogicException;

class InventoryObserver
{
    /**
     * Handle the Inventory "creating" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     *
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
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
     * Handle the Inventory "deleting" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     *
     * @throws \LogicException
     */
    public function deleting(Inventory $inventory)
    {
        if (! $inventory->isReserved()) {
            return;
        }

        if (
            $inventory->isFulfilled() &&
            $inventory->order->status->isFulfilled() &&
            $inventory->isForceDeleting() === false
        ) {
            return;
        }

        throw new LogicException('A reserved inventory item can not be deleted.');
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
     * Handle the Inventory "restoring" event.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     *
     * @throws \LogicException
     */
    public function restoring(Inventory $inventory)
    {
        if (! $inventory->isReserved()) {
            return;
        }

        throw new LogicException('This inventory item can not be restored.');
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
