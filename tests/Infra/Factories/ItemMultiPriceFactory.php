<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Factories;

use Bavix\Wallet\Test\Infra\Models\ItemMultiPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemMultiPrice>
 */
final class ItemMultiPriceFactory extends Factory
{
    protected $model = ItemMultiPrice::class;

    public function definition(): array
    {
        $priceUsd = random_int(100, 700);

        return [
            'name' => fake()
                ->domainName,
            'price' => -1,
            'quantity' => random_int(10, 100),
            'prices' => [
                'USD' => $priceUsd,
            ],
        ];
    }
}
