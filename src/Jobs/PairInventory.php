<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Bus\Queueable;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PairInventory implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable;

    /**
     * The inventory to be paired with an order line.
     *
     * @var  \Just\Warehouse\Models\Inventory
     */
    public $inventory;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $line = OrderLine::join('reservation', 'order_lines.id', '=', 'reservation.order_line_id')
            ->select('order_lines.id')
            ->where('order_lines.gtin', '=', $this->inventory->gtin)
            ->whereNull('reservation.inventory_id')
            ->orderBy('reservation.created_at')
            ->first();

        if (! is_null($line)) {
            $line->reservation->update([
                'inventory_id' => $this->inventory->id,
            ]);
        }

        $this->inventory->release();
    }
}