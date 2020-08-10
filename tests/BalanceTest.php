<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Simple\Store;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;
use function app;

class BalanceTest extends TestCase
{

    /**
     * @return void
     */
    public function testDepositWalletExists(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $buyer->deposit(1);

        self::assertTrue($buyer->relationLoaded('wallet'));
        self::assertTrue($buyer->wallet->exists);
    }

    /**
     * @return void
     */
    public function testCanWithdraw(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertTrue($buyer->canWithdraw(0));

        $buyer->forceWithdraw(1);
        self::assertFalse($buyer->canWithdraw(0));
        self::assertTrue($buyer->canWithdraw(0, true));
    }

    /**
     * @return void
     */
    public function testWithdrawWalletExists(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        self::assertEquals($buyer->balance, 0);
        $buyer->forceWithdraw(1);

        self::assertEquals($buyer->balance, -1);
        self::assertTrue($buyer->relationLoaded('wallet'));
        self::assertTrue($buyer->wallet->exists);
        self::assertLessThan(0, $buyer->balance);
    }

    /**
     * @return void
     * @throws
     */
    public function testSimple(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();

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
     * @return void
     * @throws
     */
    public function testGetBalance(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals($wallet->balance, 0);
        self::assertTrue($wallet->exists);

        self::assertEquals(0, app(Storable::class)->getBalance($wallet));
    }

    /**
     * @return void
     * @throws
     */
    public function testFailUpdate(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals($wallet->balance, 0);
        self::assertTrue($wallet->exists);

        /**
         * @var Wallet|MockObject $mockWallet
         */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('update')->willReturn(false);
        $mockWallet->method('getKey')->willReturn($wallet->getKey());

        $result = app(CommonService::class)
            ->addBalance($mockWallet, 100);

        self::assertFalse($result);
        self::assertEquals(0, app(Storable::class)->getBalance($wallet));
    }

    /**
     * @return void
     * @throws
     */
    public function testThrowUpdate(): void
    {
        $this->expectException(PDOException::class);

        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        self::assertFalse($wallet->exists);
        self::assertEquals($wallet->balance, 0);
        self::assertTrue($wallet->exists);

        /**
         * @var Wallet|MockObject $mockWallet
         */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('update')->willThrowException(new PDOException());
        $mockWallet->method('getKey')->willReturn($wallet->getKey());

        app(CommonService::class)
            ->addBalance($mockWallet, 100);
    }

    /**
     * @throws
     */
    public function testArtisanRefresh(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallets = \range('a', 'z');
        $sums = [];
        $ids = [];
        foreach ($wallets as $name) {
            $wallet = $user->createWallet(['name' => $name]);
            $ids[] = $wallet->id;
            $sums[$name] = 0;
            $operations = \random_int(1, 10);
            for ($i = 0; $i < $operations; $i++) {
                $amount = \random_int(10, 10000);
                $confirmed = (bool)\random_int(0, 1);
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

        // fresh balance
        DB::table(config('wallet.wallet.table'))
            ->update(['balance' => 0]);

        $this->artisan('wallet:refresh');
        Wallet::query()->whereKey($ids)->each(function (Wallet $wallet) use ($sums) {
            self::assertEquals($sums[$wallet->name], $wallet->balance);
        });
    }

    /**
     * @return void
     * @see https://github.com/bavix/laravel-wallet/issues/49
     */
    public function testForceUpdate(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        self::assertEquals($wallet->balance, 0);

        $wallet->deposit(1000);
        self::assertEquals($wallet->balance, 1000);

        Wallet::whereKey($buyer->wallet->getKey())
            ->update(['balance' => 10]);

        /**
         * Create a state when the cache is empty.
         * For example, something went wrong and your database has incorrect data.
         * Unfortunately, the library will work with what is.
         * But there is an opportunity to recount the balance.
         *
         * Here is an example:
         */
        app()->singleton(Storable::class, Store::class);
        self::assertEquals($wallet->getRawOriginal('balance'), 1000);

        /**
         * We load the model from the base and our balance is 10.
         */
        $wallet->refresh();
        self::assertEquals($wallet->balance, 10);
        self::assertEquals($wallet->getRawOriginal('balance'), 10);

        /**
         * Now we fill the cache with relevant data (PS, the data inside the model will be updated).
         */
        $wallet->refreshBalance();
        self::assertEquals($wallet->balance, 1000);
        self::assertEquals($wallet->getRawOriginal('balance'), 1000);
    }

}
