<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Models\States\Order\Created;
use Just\Warehouse\Models\States\Order\Hold;
use LogicException;

class OrderLineObserver
{
    /**
     * Handle the OrderLine "creating" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     *
     * @throws \InvalidGtinException
     */
    public function creating(OrderLine $line)
    {
        if (! is_gtin($line->gtin)) {
            throw new InvalidGtinException;
        }

        if (! $line->order->status->isOneOf([Created::class, Hold::class])) {
            throw new LogicException('An order line can not be created.');
        }
    }

    /**
     * Handle the OrderLine "created" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function created(OrderLine $line)
    {
        OrderLineCreated::dispatch($line);
    }

    /**
     * Handle the OrderLine "deleting" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     *
     * @throws \LogicException
     */
    public function deleting(OrderLine $line)
    {
        if (! $line->order->status->isOneOf([Created::class, Hold::class])) {
            throw new LogicException('This order line can not be deleted.');
        }

        ReleaseOrderLine::dispatchNow($line);
    }

    /**
     * Handle the OrderLine "updating" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     *
     * @throws \LogicException
     */
    public function updating(OrderLine $line)
    {
        throw new LogicException('An order line can not be updated.');
    }
}
