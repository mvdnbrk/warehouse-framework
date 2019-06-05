<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Bus\Queueable;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReleaseOrderLine implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable;

    /**
     * The order line to be released from reservation.
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
        tap($this->line->inventory, function ($inventory) {
            if (is_null($inventory)) {
                return $this->line->release();
            }

            $this->line->reservation->update([
                'order_line_id' => null,
            ]);

            PairInventory::dispatch($inventory);
        });
    }
}
