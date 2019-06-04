<?php

namespace Just\Warehouse;

trait EventMap
{
    /**
     * All of the Warehouse event / listener mappings.
     *
     * @var array
     */
    protected $events = [
        Events\InventoryCreated::class => [
            Listeners\ScheduleInventoryPairing::class,
        ],

        Events\OrderLineCreated::class => [
            Listeners\AttemptToFulfillOrderline::class,
        ],
    ];
}
