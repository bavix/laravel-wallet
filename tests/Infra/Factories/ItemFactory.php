<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Factories;

use Bavix\Wallet\Test\Infra\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @throws
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->domainName,
            'price' => random_int(1, 100),
            'quantity' => random_int(0, 10),
        ];
    }
}
