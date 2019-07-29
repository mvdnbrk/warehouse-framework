<?php

namespace Just\Warehouse\Jobs;

use Just\Warehouse\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TransitionOrderStatus
{
    use Dispatchable, SerializesModels;

    /**
     * The order which is transitioning status.
     *
     * @var \Just\Warehouse\Models\Order
     */
    public $order;

    /**
     * The new status to transition to.
     *
     * @var string
     */
    protected $newStatus;

    /**
     * Create a new job instance.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->newStatus = 'open';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->lines->isEmpty()) {
            return;
        }

        $this->order->lines->each(function ($line) {
            if (! $line->isFulfilled()) {
                $this->newStatus = 'backorder';

                return false;
            }
        });

        if (! $this->order->isValidTransition($this->order->status, $this->newStatus)) {
            return;
        }

        $this->order->update([
            'status' => $this->newStatus,
        ]);
    }
}
