<?php

use Just\Warehouse\Models\Inventory;

class InventoryFactory
{
    public function create(array $overrides = [])
    {
        return factory(Inventory::class)->create($overrides);
    }

    public function make(array $overrides = [])
    {
        return factory(Inventory::class)->make(
            array_merge($overrides, ['location_id' => null])
        );
    }
}
