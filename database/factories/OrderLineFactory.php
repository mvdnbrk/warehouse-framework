<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;

$factory->define(OrderLine::class, function (Faker $faker) {
    return [
        'order_id' => function () {
            return factory(Order::class)->create()->id;
        },
        'gtin' => $faker->ean13,
    ];
});
