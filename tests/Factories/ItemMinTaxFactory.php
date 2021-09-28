<?php

namespace Bavix\Wallet\Test\Factories;

use Bavix\Wallet\Test\Models\ItemMinTax;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemMinTaxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemMinTax::class;

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
