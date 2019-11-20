<?php

use Faker\Generator as Faker;
use Just\Warehouse\Models\Location;

$factory->define(Location::class, function (Faker $faker) {
    return [
        'name' => $faker->randomNumber.'-'.$faker->randomDigit.'-'.$faker->randomLetter,
    ];
});
