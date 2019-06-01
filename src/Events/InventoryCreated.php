<?php

namespace Just\Warehouse\Events;

use Just\Warehouse\Models\Inventory;
use Illuminate\Queue\SerializesModels;

class InventoryCreated
{
    use SerializesModels;

    /**
     * The inventory model that was created.
     *
     * @var \Just\Warehouse\Models\Inventory
     */
    public $inventory;

    /**
     * Create a new event instance.
     *
     * @param  \Just\Warehouse\Models\Inventory  $inventory
     * @return void
     */
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }
}
