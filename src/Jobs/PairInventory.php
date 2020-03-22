<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Models\States\Order\Backorder;

class PairInventory implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The inventory to be paired with an order line.
     *
     * @var \Just\Warehouse\Models\Inventory
     */
    public $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function handle(): void
    {
        $line = OrderLine::join('reservation', 'order_lines.id', '=', 'reservation.order_line_id')
            ->select(['order_lines.id', 'order_lines.order_id'])
            ->where('order_lines.gtin', '=', $this->inventory->gtin)
            ->whereNull('reservation.inventory_id')
            ->orderBy('reservation.created_at')
            ->first();

        if (! is_null($line)) {
            $this->inventory->release();

            $line->reservation->update([
                'inventory_id' => $this->inventory->id,
            ]);

            if ($line->order->status->is(Backorder::class)) {
                $line->order->process();
            }

            return;
        }

        $this->inventory->release();
    }
}
