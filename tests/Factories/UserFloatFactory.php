<?php

namespace Bavix\Wallet\Test\Factories;

use Bavix\Wallet\Test\Models\UserFloat;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFloatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserFloat::class;

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
