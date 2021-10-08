<?php

namespace Bavix\Wallet\Test;

use function app;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Common\Services\WalletAdjustmentFailedService;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class BalanceTest extends TestCase
{
    public function testDepositWalletExists(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $buyer->deposit(1);

        self::assertTrue($buyer->relationLoaded('wallet'));
        self::assertTrue($buyer->wallet->exists);
    }

    public function testCheckType(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $buyer->deposit(1000);

        self::assertIsString($buyer->balance);
        self::assertIsString($buyer->wallet->balanceFloat);

        self::assertIsInt($buyer->balanceInt);

        self::assertSame('1000', $buyer->balance);
        self::assertSame('10.00', $buyer->wallet->balanceFloat);

        self::assertSame(1000, $buyer->balanceInt);
    }

    public function testCanWithdraw(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertTrue($buyer->canWithdraw(0));

        $buyer->forceWithdraw(1);
        self::assertFalse($buyer->canWithdraw(0));
        self::assertTrue($buyer->canWithdraw(0, true));
    }

    public function testWithdrawWalletExists(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        self::assertEquals($buyer->balance, 0);
        $buyer->forceWithdraw(1);

        self::assertEquals($buyer->balance, -1);
        self::assertTrue($buyer->relationLoaded('wallet'));
        self::assertTrue($buyer->wallet->exists);
        self::assertLessThan(0, $buyer->balance);
    }

    /**
     * @throws
     */
    public function testSimple(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals($wallet->balance, 0);
        self::assertTrue($wallet->exists);

        $wallet->deposit(1000);
        self::assertEquals($wallet->balance, 1000);

        $result = app(CommonService::class)->addBalance($wallet, 100);
        self::assertTrue($result);

        self::assertEquals($wallet->balance, 1100);
        self::assertTrue($wallet->refreshBalance());

        self::assertEquals($wallet->balance, 1000);

        $key = $wallet->getKey();
        self::assertTrue($wallet->delete());
        self::assertFalse($wallet->exists);
        self::assertEquals($wallet->getKey(), $key);
        $result = app(CommonService::class)->addBalance($wallet, 100);
        self::assertTrue($result); // automatic create default wallet

        $wallet->refreshBalance();
        $balance = 0;
        if ($wallet->getConnection() instanceof SQLiteConnection) {
            $balance = 1000;
        }

        self::assertEquals($wallet->balance, $balance);

        $wallet->deposit(1);
        self::assertEquals($wallet->balance, $balance + 1);
    }

    /**
     * @throws
     */
    public function testGetBalance(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals($wallet->balance, 0);
        self::assertTrue($wallet->exists);

        self::assertEquals(0, app(Storable::class)->getBalance($wallet));
    }

    /**
     * @throws
     */
    public function testFailUpdate(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals(0, $wallet->balance);
        self::assertTrue($wallet->exists);

        /** @var MockObject|Wallet $mockQuery */
        $mockQuery = $this->createMock(\get_class($wallet->newQuery()));
        $mockQuery->method('whereKey')->willReturn($mockQuery);
        $mockQuery->method('update')->willReturn(false);

        /** @var MockObject|Wallet $mockWallet */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('newQuery')->willReturn($mockQuery);
        $mockWallet->method('getKey')->willReturn($wallet->getKey());

        $result = app(CommonService::class)
            ->addBalance($mockWallet, 100)
        ;

        self::assertFalse($result);
        self::assertEquals(0, app(Storable::class)->getBalance($wallet));
    }

    /**
     * @throws
     */
    public function testThrowUpdate(): void
    {
        $this->expectException(PDOException::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals(0, $wallet->balance);
        self::assertTrue($wallet->exists);

        /** @var MockObject|Wallet $mockQuery */
        $mockQuery = $this->createMock(\get_class($wallet->newQuery()));
        $mockQuery->method('whereKey')->willReturn($mockQuery);
        $mockQuery->method('update')->willThrowException(new PDOException());

        /** @var MockObject|Wallet $mockWallet */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('newQuery')->willReturn($mockQuery);
        $mockWallet->method('getKey')->willReturn($wallet->getKey());

        app(CommonService::class)
            ->addBalance($mockWallet, 100)
        ;
    }

    /**
     * @throws
     */
    public function testArtisanRefresh(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $wallets = \range('a', 'z');
        $sums = [];
        $ids = [];
        foreach ($wallets as $name) {
            $wallet = $user->createWallet(['name' => $name]);
            $ids[] = $wallet->id;
            $sums[$name] = 0;
            $operations = \random_int(1, 10);
            for ($i = 0; $i < $operations; ++$i) {
                $amount = \random_int(10, 10000);
                $confirmed = (bool) \random_int(0, 1);
                $deposit = $wallet->deposit($amount, null, $confirmed);
                self::assertIsInt($deposit->wallet_id);

                if ($confirmed) {
                    $sums[$name] += $amount;
                }

                self::assertEquals($amount, $deposit->amount);
                self::assertEquals($confirmed, $deposit->confirmed);
                self::assertEquals($sums[$name], $wallet->balance);
            }
        }

        /**
         * Check for the number of created wallets.
         * Make sure you didn't accidentally create the default wallet.
         *
         * @see https://github.com/bavix/laravel-wallet/issues/218
         */
        self::assertCount(count($wallets), $user->wallets);

        // fresh balance
        DB::table(config('wallet.wallet.table'))
            ->update(['balance' => 0])
        ;

        $this->artisan('wallet:refresh');
        Wallet::query()->whereKey($ids)->each(function (Wallet $wallet) use ($sums) {
            self::assertEquals($sums[$wallet->name], $wallet->balance);
        });
    }

    public function testEqualWallet(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $wallet->deposit(1000);
        self::assertEquals(1000, $wallet->balance);
        self::assertEquals(1000, $wallet->wallet->balance);
        self::assertEquals($wallet->getKey(), $wallet->wallet->getKey());
        self::assertEquals($wallet->getKey(), $wallet->wallet->wallet->getKey());
        self::assertEquals($wallet->getKey(), $wallet->wallet->wallet->wallet->getKey());
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/49
     */
    public function testForceUpdate(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $wallet->deposit(1000);
        self::assertEquals(1000, $wallet->balance);

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
        app(Storable::class)->fresh();
        self::assertEquals(1000, $wallet->getRawOriginal('balance'));

        /**
         * We load the model from the base and our balance is 10.
         */
        $wallet->refresh();
        self::assertEquals(10, $wallet->balance);
        self::assertEquals(10, $wallet->getRawOriginal('balance'));

        /**
         * Now we fill the cache with relevant data (PS, the data inside the model will be updated).
         */
        $wallet->refreshBalance();
        self::assertEquals(1000, $wallet->balance);
        self::assertEquals(1000, $wallet->getRawOriginal('balance'));
    }

    /**
     * @dataProvider providerAdjustment
     */
    public function testAdjustment(int $account, int $adjust): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $wallet->deposit($account);
        self::assertEquals($account, $wallet->balance);

        Wallet::whereKey($buyer->wallet->getKey())
            ->update(['balance' => $adjust])
        ;

        /**
         * Create a state when the cache is empty.
         * For example, something went wrong and your database has incorrect data.
         * Unfortunately, the library will work with what is.
         * But there is an opportunity to recount the balance.
         *
         * Here is an example:
         */
        app(Storable::class)->fresh();
        self::assertEquals($account, $wallet->getRawOriginal('balance'));

        /**
         * We load the model from the base and our balance is 10.
         */
        $wallet->refresh();
        self::assertEquals($adjust, $wallet->balance);
        self::assertEquals($adjust, $wallet->getRawOriginal('balance'));

        /**
         * Now we fill the cache with relevant data (PS, the data inside the model will be updated).
         */
        $wallet->adjustmentBalance();
        self::assertEquals($adjust, $wallet->balance);
        self::assertEquals($adjust, $wallet->getRawOriginal('balance'));

        /**
         * Reapply, just in case...
         */
        $wallet->refreshBalance();
        self::assertEquals($adjust, $wallet->balance);
        self::assertEquals($adjust, $wallet->getRawOriginal('balance'));
    }

    /**
     * @dataProvider providerAdjustment
     */
    public function testAdjustmentFailed(int $account, int $adjust): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $wallet = $buyer->wallet;

        self::assertEquals(0, $wallet->balance);

        $wallet->deposit($account);
        self::assertEquals($account, $wallet->balance);

        Wallet::whereKey($buyer->wallet->getKey())
            ->update(['balance' => $adjust])
        ;

        app()->singleton(WalletService::class, WalletAdjustmentFailedService::class);
        self::assertFalse($wallet->adjustmentBalance());
    }

    /**
     * @return int[][]
     */
    public function providerAdjustment(): array
    {
        return [
            [2000, 1000],
            [1000, 2000],
            [2000, 2000],
        ];
    }
}
