<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Order;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'order_number' => $faker->numberBetween(1000, 9999),
    ];
});

$factory->state(Order::class, 'backorder', []);

$factory->state(Order::class, 'fulfilled', []);

$factory->state(Order::class, 'open', []);

$factory->state(Order::class, 'hold', []);
