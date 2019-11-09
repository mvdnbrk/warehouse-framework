<?php

use Faker\Factory as Faker;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Location;

class LocationFactory
{
    public $gtins = [];

    public function withInventory($value)
    {
        if (is_int($value)) {
            $this->gtins = array_map(function () {
                return Faker::create()->ean13;
            }, range(1, $value));

            return $this;
        }

        $this->gtins = is_array($value) ? $value : func_get_args();

        return $this;
    }

    public function create(array $overrides = [])
    {
        return tap(factory(Location::class)->create($overrides), function ($location) {
            foreach ($this->gtins as $gtin) {
                factory(Inventory::class)->create([
                    'gtin' => $gtin,
                    'location_id' => $location->id,
                ]);
            }
        });
    }

    public function make(array $overrides = [])
    {
        return factory(Location::class)->make($overrides);
    }
}
