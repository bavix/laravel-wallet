<?php

namespace Bavix\Wallet\Test\Factories;

use Bavix\Wallet\Test\Models\ItemDiscountTax;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemDiscountTaxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemDiscountTax::class;

    /**
     * Define the model's default state.
     *
     * @throws
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->domainName,
            'price' => 250,
            'quantity' => 90,
        ];
    }
}
