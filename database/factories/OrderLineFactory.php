<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;

$factory->define(OrderLine::class, function (Faker $faker) {
    return [
        'order_id' => factory(Order::class),
        'gtin' => $faker->ean13,
    ];
});
