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
        ],

        Events\OrderLineCreated::class => [
        ],
    ];
}
