<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Open;

class TransitionOrderStatus implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The order which is transitioning status.
     *
     * @var \Just\Warehouse\Models\Order
     */
    public $order;

    /**
     * The new status to transition to.
     *
     * @var \Just\Warehouse\Models\States\Order\OrderState
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
        $this->newStatus = Open::class;
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
                $this->newStatus = Backorder::class;

                return false;
            }
        });

        if ($this->order->status->is($this->newStatus)) {
            return;
        }

        $this->order->setExpiresAtAttribute(0);
        $this->order->status->transitionTo($this->newStatus);
    }
}
