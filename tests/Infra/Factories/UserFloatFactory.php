<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Factories;

use Bavix\Wallet\Test\Infra\Models\UserFloat;
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
