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
final class TransactionsFilterTest extends TestCase
{
    public function testMetaAccount(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $buyer->deposit(1000); // without meta

        $buyer->withdraw(100, [
            'account' => 'customers',
        ]);
        $buyer->withdraw(150, [
            'account' => 'expenses',
        ]);
        $buyer->deposit(500, [
            'account' => 'vendors',
        ]);

        self::assertSame(4, $buyer->transactions()->count());

        $nullable = $buyer->transactions()
            ->whereNull('meta')
            ->count()
        ;
        $customers = $buyer->transactions()
            ->where('meta->account', 'customers')
            ->count()
        ;
        $expenses = $buyer->transactions()
            ->where('meta->account', 'expenses')
            ->count()
        ;
        $vendors = $buyer->transactions()
            ->where('meta->account', 'vendors')
            ->count()
        ;

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

    public function testTransferMeta(): void
    {
        /**
         * @var Buyer $buyer1
         * @var Buyer $buyer2
         */
        [$buyer1, $buyer2] = BuyerFactory::times(2)->create();
        $buyer1->deposit(1000);

        self::assertSame(1000, $buyer1->balanceInt);

        $buyer1->transfer($buyer2, 500, [
            'type' => 'credit',
        ]);

        self::assertSame(500, $buyer1->balanceInt);
        self::assertSame(500, $buyer2->balanceInt);

        self::assertSame(2, $buyer1->transactions()->count());
        self::assertSame(1, $buyer2->transactions()->count());

        $credits1 = $buyer1->transactions()
            ->where('meta->type', 'credit')
            ->count()
        ;

        $credits2 = $buyer2->transactions()
            ->where('meta->type', 'credit')
            ->count()
        ;

        self::assertSame(1, $credits1);
        self::assertSame(1, $credits2);
    }
}
