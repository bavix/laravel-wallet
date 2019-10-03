<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\UserMulti;
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
        $this->assertFalse($buyer->relationLoaded('wallet'));
        $buyer->deposit(1);

        $this->assertTrue($buyer->relationLoaded('wallet'));
        $this->assertTrue($buyer->wallet->exists);
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
        $this->assertTrue($buyer->canWithdraw(0));

        $buyer->forceWithdraw(1);
        $this->assertFalse($buyer->canWithdraw(0));
        $this->assertTrue($buyer->canWithdraw(0, true));
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
        $this->assertFalse($buyer->relationLoaded('wallet'));
        $this->assertEquals($buyer->balance, 0);
        $buyer->forceWithdraw(1);

        $this->assertEquals($buyer->balance, -1);
        $this->assertTrue($buyer->relationLoaded('wallet'));
        $this->assertTrue($buyer->wallet->exists);
        $this->assertLessThan(0, $buyer->balance);
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

        $this->assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        $this->assertFalse($wallet->exists);
        $this->assertEquals($wallet->balance, 0);
        $this->assertTrue($wallet->exists);

        $wallet->deposit(1000);
        $this->assertEquals($wallet->balance, 1000);

        $result = app(CommonService::class)->addBalance($wallet, 100);
        $this->assertTrue($result);

        $this->assertEquals($wallet->balance, 1100);
        $this->assertTrue($wallet->refreshBalance());

        $this->assertEquals($wallet->balance, 1000);

        $this->assertTrue($wallet->delete());
        $this->assertFalse($wallet->exists);
        $result = app(CommonService::class)->addBalance($wallet, 100);
        $this->assertTrue($result); // automatic create default wallet

        $wallet->refreshBalance();
        $this->assertEquals($wallet->balance, 1000);

        $wallet->deposit(1);
        $this->assertEquals($wallet->balance, 1001);
    }

    /**
     * @return void
     * @throws
     * @deprecated
     */
    public function testGetBalance(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $this->assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        $this->assertFalse($wallet->exists);
        $this->assertEquals($wallet->balance, 0);
        $this->assertTrue($wallet->exists);

        $this->assertEquals(0, app(Storable::class)->getBalance($wallet));
        $this->assertEquals(0, app(WalletService::class)->getBalance($wallet));
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
        $this->assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        $this->assertFalse($wallet->exists);
        $this->assertEquals($wallet->balance, 0);
        $this->assertTrue($wallet->exists);

        /**
         * @var Wallet|MockObject $mockWallet
         */
        $mockWallet = $this->createMock(\get_class($wallet));
        $mockWallet->method('update')->willReturn(false);
        $mockWallet->method('getKey')->willReturn($wallet->getKey());

        $result = app(CommonService::class)
            ->addBalance($mockWallet, 100);

        $this->assertFalse($result);
        $this->assertEquals(0, app(Storable::class)->getBalance($wallet));
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
        $this->assertFalse($buyer->relationLoaded('wallet'));
        $wallet = $buyer->wallet;

        $this->assertFalse($wallet->exists);
        $this->assertEquals($wallet->balance, 0);
        $this->assertTrue($wallet->exists);

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

                if ($confirmed) {
                    $sums[$name] += $amount;
                }

                $this->assertEquals($amount, $deposit->amount);
                $this->assertEquals($confirmed, $deposit->confirmed);
                $this->assertEquals($sums[$name], $wallet->balance);
            }
        }

        // fresh balance
        app(ProxyService::class)->fresh();
        DB::table(config('wallet.wallet.table'))
            ->update(['balance' => 0]);

        $this->artisan('wallet:refresh');
        Wallet::query()->whereKey($ids)->each(function (Wallet $wallet) use ($sums) {
            $this->assertEquals($sums[$wallet->name], $wallet->balance);
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

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(1000);
        $this->assertEquals($wallet->balance, 1000);

        Wallet::whereKey($buyer->wallet->getKey())
            ->update(['balance' => 10]);

        app(ProxyService::class)->fresh();

        $wallet->refresh();
        $this->assertEquals($wallet->balance, 10);

        $wallet->refreshBalance();
        $this->assertEquals($wallet->balance, 1000);
    }

}
