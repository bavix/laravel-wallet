<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Facades\DB;
use Spatie\Fork\Fork;

/**
 * @internal
 */
final class RaceCondTest extends TestCase
{
    public function testSimple(): void
    {
        if (env('CACHE_DRIVER') !== 'redis') {
            $this->markTestSkipped();
        }

        $this->app->get('config')->set('wallet.lock.seconds', 30);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $callback = static function () use ($buyer): void {
            while (true) {
                try {
                    $buyer->getConnection()->reconnect();
                    $buyer->deposit(10);
                } catch (\Throwable $throwable) {
                    usleep(10_000);
                    continue;
                }
                break;
            }
        };

        Fork::new()
            ->concurrent(4)
            ->run(...array_fill(1, 100, $callback));

        self::assertSame(1_000, $buyer->balanceInt);
    }
}
