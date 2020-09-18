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
     * @throws \Just\Warehouse\Exceptions\InvalidGtinException
     * @throws \LogicException
     */
    public function creating(OrderLine $line): void
    {
        if (! is_gtin($line->gtin)) {
            throw new InvalidGtinException;
        }

        if (! $line->order->status->isOneOf([Created::class, Hold::class])) {
            throw new LogicException('An order line can not be created.');
        }
    }

    public function created(OrderLine $line): void
    {
        OrderLineCreated::dispatch($line);
    }

    /**
     * @throws \LogicException
     */
    public function deleting(OrderLine $line): void
    {
        if (! $line->order->status->isOneOf([Created::class, Hold::class])) {
            throw new LogicException('This order line can not be deleted.');
        }

        ReleaseOrderLine::dispatchNow($line);
    }

    /**
     * @throws \LogicException
     */
    public function updating(): void
    {
        throw new LogicException('An order line can not be updated.');
    }
}
