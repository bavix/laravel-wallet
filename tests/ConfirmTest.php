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

        $this->assertEquals(0, $wallet->balance);

        $transaction = $wallet->deposit(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals(0, $wallet->balance);
        $this->assertFalse($transaction->confirmed);

        $wallet->confirm($transaction);
        $this->assertEquals($transaction->amount, $wallet->balance);
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

        $this->assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals(0, $wallet->balance);
        $this->assertFalse($transaction->confirmed);

        $wallet->safeConfirm($transaction);
        $this->assertEquals(0, $wallet->balance);
        $this->assertFalse($transaction->confirmed);
    }

    /**
     * @return void
     */
    public function testSafeResetConfirm(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'confirmed']);
        $this->assertEquals(-1000, $wallet->balance);
        $this->assertTrue($transaction->confirmed);

        $wallet->safeResetConfirm($transaction);
        $this->assertEquals(0, $wallet->balance);
        $this->assertFalse($transaction->confirmed);
    }

    /**
     * @see https://github.com/bavix/laravel-wallet/issues/134
     * @return void
     */
    public function testWithdraw(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;
        $wallet->deposit(100);

        $this->assertEquals(100, $wallet->balance);

        $transaction = $wallet->withdraw(50, ['desc' => 'unconfirmed'], false);
        $this->assertEquals(100, $wallet->balance);
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

        $this->assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals(0, $wallet->balance);
        $this->assertFalse($transaction->confirmed);

        $wallet->forceConfirm($transaction);
        $this->assertEquals($transaction->amount, $wallet->balance);
        $this->assertTrue($transaction->confirmed);
    }

    /**
     * @return void
     */
    public function testUnconfirmed(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals(0, $wallet->balance);

        $transaction = $wallet->forceWithdraw(1000, ['desc' => 'confirmed']);
        $this->assertEquals(-1000, $wallet->balance);
        $this->assertTrue($transaction->confirmed);

        $wallet->resetConfirm($transaction);
        $this->assertEquals(0, $wallet->balance);
        $this->assertFalse($transaction->confirmed);
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

        $this->assertEquals(0, $wallet->balance);

        $transaction = $wallet->deposit(1000);
        $this->assertEquals(1000, $wallet->balance);
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

        $this->assertEquals(0, $firstWallet->balance);

        $transaction = $firstWallet->deposit(1000, ['desc' => 'unconfirmed'], false);
        $this->assertEquals(0, $firstWallet->balance);
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
        $this->assertEquals($transaction->wallet->id, $userConfirm->wallet->id);
        $this->assertEquals($transaction->payable_id, $userConfirm->id);
        $this->assertInstanceOf(UserConfirm::class, $transaction->payable);
        $this->assertFalse($transaction->confirmed);

        $this->assertTrue($userConfirm->confirm($transaction));
        $this->assertTrue($transaction->confirmed);
    }

}
