<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Events\InventoryCreated;

class ReserveInventory
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\InventoryCreated  $event
     * @return void
     */
    public function handle(InventoryCreated $event)
    {
        $event->inventory->reserve();
    }
}
