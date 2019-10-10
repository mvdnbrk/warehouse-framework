<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\OrderLine;

class ReleaseOrderLine implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The order line to be released from reservation.
     *
     * @var \Just\Warehouse\Models\OrderLine
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
        tap($this->line->inventory, function ($inventory) {
            if (is_null($inventory) || $inventory->trashed()) {
                return $this->line->release();
            }

            $this->line->reservation->update([
                'order_line_id' => null,
            ]);

            PairInventory::dispatch($inventory);
        });
    }
}
