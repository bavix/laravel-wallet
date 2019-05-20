<?php

use Bavix\Wallet\Test\Models\Item;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Item::class, function (Faker $faker) {
    return [
        'name' => $faker->domainName,
        'price' => random_int(1, 100),
        'quantity' => random_int(0, 10),
    ];
});
