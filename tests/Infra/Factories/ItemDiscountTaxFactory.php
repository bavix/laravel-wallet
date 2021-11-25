<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Factories;

use Bavix\Wallet\Test\Infra\Models\ItemDiscountTax;
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
