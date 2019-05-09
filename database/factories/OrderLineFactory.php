<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Order;
use Just\Warehouse\Models\OrderLine;

$factory->define(OrderLine::class, function (Faker $faker) {
    return [
        'gtin' => $faker->ean13,
        'order_id' => function () {
            return factory(Order::class)->create()->id;
        },
    ];
});
