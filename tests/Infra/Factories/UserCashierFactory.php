<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Factories;

use Bavix\Wallet\Test\Infra\Models\UserCashier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserCashier>
 */
final class UserCashierFactory extends Factory
{
    protected $model = UserCashier::class;

    public function definition(): array
    {
        return [
            'name' => fake()
                ->name,
            'email' => fake()
                ->unique()
                ->safeEmail,
        ];
    }
}
