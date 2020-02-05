<?php

namespace Just\Warehouse;

trait EventMap
{
    /**
     * All of the event / listener mappings.
     *
     * @var array
     */
    protected $events = [
        Events\OrderFulfilled::class => [
            Listeners\DeleteInventoryForOrder::class,
        ],

        Events\InventoryCreated::class => [
            Listeners\ScheduleInventoryPairing::class,
        ],

        Events\OrderLineCreated::class => [
            Listeners\ScheduleOrderLinePairing::class,
        ],
    ];
}
