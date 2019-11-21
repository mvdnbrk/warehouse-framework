<?php

use Illuminate\Support\Facades\Event;
use Just\Warehouse\Models\Inventory;

class InventoryFactory
{
    protected $states = [];

    public function state($value)
    {
        $this->states([$value]);

        return $this;
    }

    public function create(array $overrides = [])
    {
        if (! in_array('deleted', $this->states)) {
            return factory(Inventory::class)->states($this->states)->create($overrides);
        };

        return Event::fakeFor(function () use ($overrides) {
            return factory(Inventory::class)->states($this->states)->create($overrides);
        });
    }

    public function make(array $overrides = [])
    {
        return factory(Inventory::class)->make(
            array_merge($overrides, ['location_id' => null])
        );
    }

    protected function states($value)
    {
        $this->states = is_array($value) ? $value : func_get_args();

        return $this;
    }
}
