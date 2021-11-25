<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use function now;

/**
 * @internal
 */
class TransactionsFilterTest extends TestCase
{
    public function testMetaAccount(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $buyer->deposit(1000); // without meta

        $buyer->withdraw(100, ['account' => 'customers']);
        $buyer->withdraw(150, ['account' => 'expenses']);
        $buyer->deposit(500, ['account' => 'vendors']);

        self::assertSame(4, $buyer->transactions()->count());

        $nullable = $buyer->transactions()->whereNull('meta')->count();
        $customers = $buyer->transactions()->where('meta->account', 'customers')->count();
        $expenses = $buyer->transactions()->where('meta->account', 'expenses')->count();
        $vendors = $buyer->transactions()->where('meta->account', 'vendors')->count();

        self::assertSame(1, $nullable);
        self::assertSame(1, $customers);
        self::assertSame(1, $expenses);
        self::assertSame(1, $vendors);

        $countByPeriods = $buyer->transactions()
            ->whereIn('meta->account', ['customers', 'expenses', 'vendors'])
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->count()
        ;

        self::assertSame(3, $countByPeriods);
    }
}
