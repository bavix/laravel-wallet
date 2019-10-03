<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\UserConfirm;

class ConfirmTest extends TestCase
{

    /**
     * @return void
     */
    public function testSimple(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals($wallet->balance, 0);

        $transaction = $wallet->deposit(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals($wallet->balance, 0);
        $this->assertFalse($transaction->confirmed);

        $wallet->confirm($transaction);
        $this->assertEquals($wallet->balance, $transaction->amount);
        $this->assertTrue($transaction->confirmed);
    }

    /**
     * @return void
     */
    public function testSafe(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals($wallet->balance, 0);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals($wallet->balance, 0);
        $this->assertFalse($transaction->confirmed);

        $wallet->safeConfirm($transaction);
        $this->assertEquals($wallet->balance, 0);
        $this->assertFalse($transaction->confirmed);
    }

    /**
     * @return void
     */
    public function testForce(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals($wallet->balance, 0);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals($wallet->balance, 0);
        $this->assertFalse($transaction->confirmed);

        $wallet->forceConfirm($transaction);
        $this->assertEquals($wallet->balance, $transaction->amount);
        $this->assertTrue($transaction->confirmed);
    }

    /**
     * @return void
     */
    public function testConfirmedInvalid(): void
    {
        $this->expectException(ConfirmedInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.confirmed_invalid'));

        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals($wallet->balance, 0);

        $transaction = $wallet->deposit(1000);
        $this->assertEquals($wallet->balance, 1000);
        $this->assertTrue($transaction->confirmed);

        $wallet->confirm($transaction);
    }

    /**
     * @return void
     */
    public function testWalletOwnerInvalid(): void
    {
        $this->expectException(WalletOwnerInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.owner_invalid'));

        /**
         * @var Buyer $first
         * @var Buyer $second
         */
        list($first, $second) = factory(Buyer::class, 2)->create();
        $firstWallet = $first->wallet;
        $secondWallet = $second->wallet;

        $this->assertEquals($firstWallet->balance, 0);

        $transaction = $firstWallet->deposit(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertFalse($transaction->confirmed);

        $secondWallet->confirm($transaction);
    }

    /**
     * @return void
     */
    public function testUserConfirm(): void
    {
        /**
         * @var UserConfirm $userConfirm
         */
        $userConfirm = factory(UserConfirm::class)->create();
        $transaction = $userConfirm->deposit(100, null, false);
        $this->assertEquals($userConfirm->wallet->id, $transaction->wallet->id);
        $this->assertEquals($userConfirm->id, $transaction->payable_id);
        $this->assertInstanceOf(UserConfirm::class, $transaction->payable);
        $this->assertFalse($transaction->confirmed);

        $this->assertTrue($userConfirm->confirm($transaction));
        $this->assertTrue($transaction->confirmed);
    }

}
