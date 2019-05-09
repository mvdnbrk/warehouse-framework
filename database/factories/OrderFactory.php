<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Order;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'order_number' => $faker->numberBetween(1000, 9999),
    ];
});
