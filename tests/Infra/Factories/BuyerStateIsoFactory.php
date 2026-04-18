<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Factories;

use Bavix\Wallet\Test\Infra\Models\BuyerStateIso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BuyerStateIso>
 */
final class BuyerStateIsoFactory extends Factory
{
    protected $model = BuyerStateIso::class;

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
