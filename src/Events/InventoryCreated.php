<?php

namespace Just\Warehouse\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Just\Warehouse\Models\Inventory;

class InventoryCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The inventory model that was created.
     *
     * @var \Just\Warehouse\Models\Inventory
     */
    public $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }
}
