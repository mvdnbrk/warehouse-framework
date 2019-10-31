<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Events\OrderStatusUpdated;
use Just\Warehouse\Exceptions\InvalidOrderNumberException;
use Just\Warehouse\Jobs\PairOrderLine;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Deleted;
use LogicException;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function creating(Order $order)
    {
        if (empty($order->order_number)) {
            throw new InvalidOrderNumberException;
        }
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        if ($order->wasChanged('status')) {
            OrderStatusUpdated::dispatch($order, $order->getOriginal('status'));
        }
    }

    /**
     * Handle the Order "deleting" event.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function deleting(Order $order)
    {
        if ($order->isForceDeleting()) {
            throw new LogicException('An order can not be force deleted.');
        }
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        $order->lines->each(function ($line) {
            ReleaseOrderLine::dispatch($line);
        });

        $order->update([
            'status' => Deleted::class,
        ]);
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \Just\Warehouse\Models\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        $order->lines->each(function ($line) {
            PairOrderLine::dispatch($line);
        });

        $order->update([
            'status' => Created::class,
        ]);
    }
}
