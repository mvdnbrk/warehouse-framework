<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Bus\Queueable;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PairOrderLine implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable;

    /**
     * The order line to be paired with an inventory item.
     *
     * @var  \Just\Warehouse\Models\OrderLine
     */
    public $line;

    /**
     * Create a new job instance.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function __construct(OrderLine $line)
    {
        $this->line = $line;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $inventory = Inventory::join('reservation', 'inventories.id', '=', 'reservation.inventory_id', 'left')
            ->select('inventories.id')
            ->where('inventories.gtin', '=', $this->line->gtin)
            ->whereNull('reservation.inventory_id')
            ->orderBy('inventories.created_at')
            ->first();

        if (! is_null($inventory)) {
            $this->line->reservation->fill([
                'inventory_id' => $inventory->id,
            ]);
        }

        $this->line->reserve();
    }
}
