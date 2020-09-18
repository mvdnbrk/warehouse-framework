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
     * @throws \Just\Warehouse\Exceptions\InvalidOrderNumberException
     */
    public function creating(Order $order): void
    {
        if (empty($order->order_number)) {
            throw new InvalidOrderNumberException;
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            OrderStatusUpdated::dispatch($order, $order->getOriginal('status'));
        }
    }

    /**
     * @throws \LogicException
     */
    public function deleting(Order $order): void
    {
        if ($order->isForceDeleting()) {
            throw new LogicException('An order can not be force deleted.');
        }
    }

    public function deleted(Order $order): void
    {
        $order->lines->each(function ($line) {
            ReleaseOrderLine::dispatch($line);
        });

        $order->update([
            'expires_at' => 0,
            'status' => Deleted::class,
        ]);
    }

    public function restored(Order $order): void
    {
        $order->lines->each(function ($line) {
            PairOrderLine::dispatch($line);
        });

        $order->update([
            'status' => Created::class,
        ]);
    }
}
