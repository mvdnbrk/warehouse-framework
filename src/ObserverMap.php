<?php

namespace Just\Warehouse;

trait ObserverMap
{
    /**
     * All of the Warehouse model / oberserver mappings.
     *
     * @var array
     */
    protected $observers = [
        Models\Order::class => Observers\OrderObserver::class,
        Models\Location::class => Observers\LocationObserver::class,
        Models\Inventory::class => Observers\InventoryObserver::class,
        Models\OrderLine::class => Observers\OrderLineObserver::class,
    ];
}
