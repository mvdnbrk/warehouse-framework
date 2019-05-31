<?php

namespace Just\Warehouse\Listeners;

use Just\Warehouse\Models\OrderLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Just\Warehouse\Events\InventoryCreated;

class CheckForUnfulfilledOrderLine implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \Just\Warehouse\Events\InventoryCreated  $event
     * @return void
     */
    public function handle(InventoryCreated $event)
    {
        $line = OrderLine::join('reservation', 'order_lines.id', '=', 'reservation.order_line_id')
            ->select('order_lines.id')
            ->where('order_lines.gtin', '=', $event->inventory->gtin)
            ->whereNull('reservation.inventory_id')
            ->orderBy('reservation.created_at')
            ->first();

        if (! is_null($line)) {
            $line->reservation->update([
                'inventory_id' => $event->inventory->id,
            ]);
        }

        $event->inventory->release();
    }
}
