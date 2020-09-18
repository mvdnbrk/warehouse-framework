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
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     */
    public function creating(Inventory $inventory): void
    {
        if (! is_gtin($inventory->gtin)) {
            throw new InvalidGtinException;
        }
    }

    public function created(Inventory $inventory): void
    {
        $inventory->reserve();

        InventoryCreated::dispatch($inventory);
    }

    /**
     * @throws \LogicException
     */
    public function deleting(Inventory $inventory): void
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
     * @throws \LogicException
     */
    public function updating(Inventory $inventory): void
    {
        if ($inventory->isDirty('gtin')) {
            throw new LogicException('The GTIN attribute can not be changed.');
        }
    }

    /**
     * @throws \LogicException
     */
    public function restoring(Inventory $inventory): void
    {
        if (! $inventory->isReserved()) {
            return;
        }

        throw new LogicException('This inventory item can not be restored.');
    }

    public function restored(Inventory $inventory): void
    {
        $inventory->reserve();

        PairInventory::dispatch($inventory);
    }
}
