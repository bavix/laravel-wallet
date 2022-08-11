<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use Spatie\Fork\Fork;

/**
 * @internal
 */
final class RaceCondTest extends TestCase
{
    public function testSimple(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $callback = static function () use ($buyer): void {
            $buyer->deposit(10);
        };

        Fork::new()
            ->concurrent(100)
            ->run(...array_fill(1, 100, $callback));

        self::assertSame(1_000, $buyer->balanceInt);
    }
}
