<?php

namespace Just\Warehouse\Events;

use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\OrderLine;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class OrderLineReplaced
{
    use Dispatchable,
        SerializesModels;

    /**
     * The order for which an order line is replaced.
     *
     * @var \Just\Warehouse\Models\Order
     */
    public $order;

    /**
     * The inventory that was deleted.
     *
     * @var \Just\Warehouse\Models\Inventory
     */
    public $inventory;

    /**
     * The new order line that was created
     *
     * @var \Just\Warehouse\Models\OrderLine
     */
    public $line;

    /**
     * Create a new event instance.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function __construct(Order $order, Inventory $inventory, OrderLine $line)
    {
        $this->order = $order;
        $this->inventory = $inventory;
        $this->line = $line;
    }
}
