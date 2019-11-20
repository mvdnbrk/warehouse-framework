<?php

namespace Just\Warehouse\Observers;

use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;
use Just\Warehouse\Jobs\ReleaseOrderLine;
use Just\Warehouse\Models\OrderLine;
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
     */
    public function deleting(OrderLine $line)
    {
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
