<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
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

    public function testPagination(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $db = app(DatabaseServiceInterface::class);
        $db->transaction(function () use ($buyer): void {
            foreach (range(1, 21) as $item) {
                $buyer->deposit($item);
            }
        });

        self::assertSame(21, $buyer->transactions()->count());

        $query = Transaction::with('wallet')
            ->where('payable_id', $buyer->getKey())
            ->orderBy('created_at', 'desc')
        ;

        $page1 = (clone $query)->paginate(10, page: 1);
        self::assertCount(10, $page1->items());
        self::assertTrue($page1->hasMorePages());

        $page2 = (clone $query)->paginate(10, page: 2);
        self::assertCount(10, $page2->items());
        self::assertTrue($page2->hasMorePages());

        $page3 = (clone $query)->paginate(10, page: 3);
        self::assertCount(1, $page3->items());
        self::assertFalse($page3->hasMorePages());
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/501
     */
    public function testPagination2(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $db = app(DatabaseServiceInterface::class);
        $db->transaction(function () use ($buyer): void {
            foreach (range(1, 21) as $item) {
                $buyer->deposit($item);
            }
        });

        self::assertSame(21, $buyer->transactions()->count());

        $walletTableName = (new Wallet())->getTable();
        $transactionTableName = (new Transaction())->getTable();

        $query = Transaction::query()
            ->where(function ($query) use ($buyer, $walletTableName, $transactionTableName) {
                $query->where('payable_id', '=', $buyer->getKey())
                    ->join($walletTableName, $transactionTableName . '.wallet_id', '=', $walletTableName . '.id')
                    ->select($transactionTableName . '.*', $walletTableName . '.name')
                    ->get()
                ;
            })
            ->orderBy('created_at', 'desc')
        ;

        $page1 = (clone $query)->paginate(10, page: 1);
        self::assertCount(10, $page1->items());
        self::assertTrue($page1->hasMorePages());

        $page2 = (clone $query)->paginate(10, page: 2);
        self::assertCount(10, $page2->items());
        self::assertTrue($page2->hasMorePages());

        $page3 = (clone $query)->paginate(10, page: 3);
        self::assertCount(1, $page3->items());
        self::assertFalse($page3->hasMorePages());
    }
}
