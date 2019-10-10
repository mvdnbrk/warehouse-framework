<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Location;

$factory->define(Inventory::class, function (Faker $faker) {
    return [
        'gtin' => $faker->ean13,
        'location_id' => function () {
            return factory(Location::class)->create()->id;
        },
    ];
});
