<?php

namespace Bavix\Wallet\Test\Factories;

use Bavix\Wallet\Test\Models\UserCashier;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserCashierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserCashier::class;

    /**
     * Define the model's default state.
     *
     * @throws
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
