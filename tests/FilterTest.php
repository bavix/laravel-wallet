<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Models\Buyer;

/**
 * @internal
 */
class FilterTest extends TestCase
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

        self::assertEquals(4, $buyer->transactions()->count());

        if (version_compare(PHP_VERSION, '7.3.0') < 0) {
            self::markTestSkipped('You are using old php. Test not available.');

            return;
        }

        $customers = $buyer->transactions()->where('meta->account', 'customers')->count();
        $expenses = $buyer->transactions()->where('meta->account', 'expenses')->count();
        $vendors = $buyer->transactions()->where('meta->account', 'vendors')->count();

        self::assertEquals(1, $customers);
        self::assertEquals(1, $expenses);
        self::assertEquals(1, $vendors);

        $countByPeriods = $buyer->transactions()
            ->whereIn('meta->account', ['customers', 'expenses', 'vendors'])
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->count()
        ;

        self::assertEquals(3, $countByPeriods);
    }
}
