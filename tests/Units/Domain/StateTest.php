<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use function app;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class StateTest extends TestCase
{
    /**
     * @see https://github.com/bavix/laravel-wallet/issues/49
     */
    public function testForceUpdate(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertSame(0, $wallet->balanceInt);

        $wallet->deposit(1000);
        self::assertSame(1000, $wallet->balanceInt);
        self::assertSame(0, (int) app(RegulatorServiceInterface::class)->diff($wallet));

        Wallet::whereKey($buyer->wallet->getKey())
            ->update(['balance' => 10])
        ;

        /**
         * Create a state when the cache is empty.
         * For example, something went wrong and your database has incorrect data.
         * Unfortunately, the library will work with what is.
         * But there is an opportunity to recount the balance.
         *
         * Here is an example:
         */
        app(BookkeeperServiceInterface::class)->missing($buyer->wallet);
        self::assertSame(1000, (int) $wallet->getRawOriginal('balance'));

        /**
         * We load the model from the base and our balance is 10.
         */
        $wallet->refresh();
        self::assertSame(10, $wallet->balanceInt);
        self::assertSame(10, (int) $wallet->getRawOriginal('balance'));

        /**
         * Now we fill the cache with relevant data (PS, the data inside the model will be updated).
         */
        $wallet->refreshBalance();
        self::assertSame(1000, $wallet->balanceInt);
        self::assertSame(1000, (int) $wallet->getRawOriginal('balance'));
    }

    public function testTransactionRollback(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertSame(0, $wallet->balanceInt);
        self::assertTrue($wallet->exists);

        $bookkeeper = app(BookkeeperServiceInterface::class);
        $regulator = app(RegulatorServiceInterface::class);

        $wallet->deposit(1000);
        self::assertSame(0, (int) $regulator->diff($wallet));
        self::assertSame(1000, (int) $regulator->amount($wallet));
        self::assertSame(1000, (int) $bookkeeper->amount($wallet));
        self::assertSame(1000, $wallet->balanceInt);

        app(DatabaseServiceInterface::class)->transaction(function () use ($wallet, $regulator, $bookkeeper) {
            $wallet->deposit(10000);
            self::assertSame(10000, (int) $regulator->diff($wallet));
            self::assertSame(11000, (int) $regulator->amount($wallet));
            self::assertSame(1000, (int) $bookkeeper->amount($wallet));

            return false; // rollback
        });

        self::assertSame(0, (int) $regulator->diff($wallet));
        self::assertSame(1000, (int) $regulator->amount($wallet));
        self::assertSame(1000, (int) $bookkeeper->amount($wallet));
        self::assertSame(1000, $wallet->balanceInt);
    }

    public function testRefreshInTransaction(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $buyer->deposit(10000);

        $bookkeeper = app(BookkeeperServiceInterface::class);
        $regulator = app(RegulatorServiceInterface::class);

        $bookkeeper->increase($buyer->wallet, 100);
        self::assertSame(10100, $buyer->balanceInt);

        app(DatabaseServiceInterface::class)->transaction(function () use ($bookkeeper, $regulator, $buyer) {
            self::assertTrue($buyer->wallet->refreshBalance());
            self::assertSame(-100, (int) $regulator->diff($buyer->wallet));
            self::assertSame(10100, (int) $bookkeeper->amount($buyer->wallet));
            self::assertSame(10000, $buyer->balanceInt); // bookkeeper.amount+regulator.diff

            return false; // rollback. cancel refreshBalance
        });

        self::assertSame(0, (int) $regulator->diff($buyer->wallet));
        self::assertSame(10100, (int) $bookkeeper->amount($buyer->wallet));
        self::assertSame(10100, $buyer->balanceInt);

        app(DatabaseServiceInterface::class)->transaction(function () use ($bookkeeper, $regulator, $buyer) {
            self::assertTrue($buyer->wallet->refreshBalance());
            self::assertSame(-100, (int) $regulator->diff($buyer->wallet));
            self::assertSame(10100, (int) $bookkeeper->amount($buyer->wallet));
            self::assertSame(10000, $buyer->balanceInt); // bookkeeper.amount+regulator.diff

            return []; // if count() === 0 then rollback. cancel refreshBalance
        });

        self::assertSame(0, (int) $regulator->diff($buyer->wallet));
        self::assertSame(10100, (int) $bookkeeper->amount($buyer->wallet));
        self::assertSame(10100, $buyer->balanceInt);

        self::assertTrue($buyer->wallet->refreshBalance());

        self::assertSame(0, (int) $regulator->diff($buyer->wallet));
        self::assertSame(10000, (int) $bookkeeper->amount($buyer->wallet));
        self::assertSame(10000, $buyer->balanceInt);
    }
}
