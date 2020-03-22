<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;
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

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->newStatus = new Open($this->order);
    }

    public function handle(): void
    {
        if ($this->order->lines->isEmpty()) {
            return;
        }

        $this->order->lines->each(function (OrderLine $line) {
            if (! $line->isFulfilled()) {
                $this->newStatus = new Backorder($this->order);

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
