<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use Just\Warehouse\Models\Inventory;
use Just\Warehouse\Models\Location;

$factory->define(Inventory::class, function (Faker $faker) {
    return [
        'gtin' => $faker->ean13,
        'location_id' => factory(Location::class),
    ];
});

$factory->state(Inventory::class, 'deleted', function () {
    return [
        'deleted_at' => Carbon::now(),
    ];
});
