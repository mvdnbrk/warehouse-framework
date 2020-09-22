<?php

namespace Just\Warehouse\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Models\States\Order\Backorder;
use Just\Warehouse\Models\States\Order\Open;
use Just\Warehouse\Models\States\Order\OrderState;

class TransitionOrderStatus implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    public int $tries = 1;

    public Order $order;

    protected OrderState $newStatus;

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

        $this->order->discardExpiration();
        $this->order->status->transitionTo($this->newStatus);
    }
}
