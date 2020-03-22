<?php

namespace Just\Warehouse;

trait EventMap
{
    protected array $events = [
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
