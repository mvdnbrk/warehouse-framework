<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Location;
use Just\Warehouse\Models\Inventory;

$factory->define(Inventory::class, function (Faker $faker) {
    return [
        'gtin' => $faker->ean13,
        'location_id' =>  function () {
            return factory(Location::class)->create()->id;
        },
    ];
});

$factory->state(Inventory::class, 'reserved', function () {
    return [
        'reserved_at' => now(),
    ];
});
