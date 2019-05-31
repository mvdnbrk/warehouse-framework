<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Models\Inventory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Just\Warehouse\Events\OrderLineCreated;

class AttemptToFulfillOrderline implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\OrderLineCreated  $event
     * @return void
     */
    public function handle(OrderLineCreated $event)
    {
        $inventory = Inventory::join('reservation', 'inventories.id', '=', 'reservation.inventory_id', 'left')
            ->select('inventories.id')
            ->where('inventories.gtin', '=', $event->line->gtin)
            ->whereNull('reservation.inventory_id')
            ->orderBy('inventories.created_at')
            ->first();

        if (! is_null($inventory)) {
            $event->line->reservation->fill([
                'inventory_id' => $inventory->id,
            ]);
        }

        $event->line->reserve();
    }
}
