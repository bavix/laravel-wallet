<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Test\Models\Buyer;
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
