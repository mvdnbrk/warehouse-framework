<?php

namespace Just\Warehouse\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;

class OrderLineReplaced
{
    use Dispatchable, SerializesModels;

    /**
     * The order for which an order line is replaced.
     */
    public Order $order;

    /**
     * The inventory that was deleted.
     */
    public Inventory $inventory;

    /**
     * The new order line that was created.
     */
    public OrdeLine $line;

    public function __construct(Order $order, Inventory $inventory, OrderLine $line)
    {
        $this->order = $order;
        $this->inventory = $inventory;
        $this->line = $line;
    }
}
