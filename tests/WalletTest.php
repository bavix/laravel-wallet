<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Models\User;

class WalletTest extends TestCase
{

    /**
     * @return void
     */
    public function testDeposit(): void
    {
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);

        $user->deposit(10);
        $this->assertEquals($user->balance, 10);

        $user->deposit(10);
        $this->assertEquals($user->balance, 20);

        $user->deposit(980);
        $this->assertEquals($user->balance, 1000);

        $this->assertEquals($user->transactions()->count(), 3);

        $user->withdraw($user->balance);
        $this->assertEquals($user->balance, 0);

        $this->assertEquals(
            $user->transactions()
                ->where(['type' => Transaction::TYPE_DEPOSIT])
                ->count(),
            3
        );

        $this->assertEquals(
            $user->transactions()
                ->where(['type' => Transaction::TYPE_WITHDRAW])
                ->count(),
            1
        );

        $this->assertEquals($user->transactions()->count(), 4);
    }

    /**
     * @return void
     */
    public function testInvalidDeposit(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.price_positive'));

        /**
         * @var User $user
         */
        $user = factory(User::class)->create();
        $user->deposit(-1);
    }

    /**
     * @return void
     */
    public function testWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);

        $user->deposit(100);
        $this->assertEquals($user->balance, 100);

        $user->withdraw(10);
        $this->assertEquals($user->balance, 90);

        $user->withdraw(81);
        $this->assertEquals($user->balance, 9);

        $user->withdraw(9);
        $this->assertEquals($user->balance, 0);

        $user->withdraw(1);
    }

    /**
     * @return void
     */
    public function testInvalidWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));
        $user = factory(User::class)->create();
        $user->withdraw(-1);
    }

    /**
     * @return void
     */
    public function testInsufficientFundsWithdraw(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.insufficient_funds'));
        $user = factory(User::class)->create();
        $user->deposit(1);
        $user->withdraw(2);
    }

    /**
     * @return void
     */
    public function testTransfer(): void
    {
        /**
         * @var User $first
         * @var User $second
         */
        list($first, $second) = factory(User::class, 2)->create();
        $this->assertNotEquals($first->id, $second->id);
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);

        $first->deposit(100);
        $this->assertEquals($first->balance, 100);

        $second->deposit(100);
        $this->assertEquals($second->balance, 100);

        $first->transfer($second, 100);
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 200);

        $second->transfer($first, 100);
        $this->assertEquals($second->balance, 100);
        $this->assertEquals($first->balance, 100);

        $second->transfer($first, 100);
        $this->assertEquals($second->balance, 0);
        $this->assertEquals($first->balance, 200);

        $first->withdraw($first->balance);
        $this->assertEquals($first->balance, 0);

        $this->assertNull($first->safeTransfer($second, 100));
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);

        $this->assertNotNull($first->forceTransfer($second, 100));
        $this->assertEquals($first->balance, -100);
        $this->assertEquals($second->balance, 100);

        $this->assertNotNull($second->forceTransfer($first, 100));
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($second->balance, 0);
    }

    /**
     * @return void
     */
    public function testTransferYourself(): void
    {
        /**
         * @var User $user
         */
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);

        $user->deposit(100);
        $user->transfer($user, 100);
        $this->assertEquals($user->balance, 100);

        $user->withdraw($user->balance);
        $this->assertEquals($user->balance, 0);
    }

    /**
     * @return void
     */
    public function testBalanceIsEmpty(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /**
         * @var User $user
         */
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);
        $user->withdraw(1);
    }

    /**
     * @return void
     */
    public function testConfirmed(): void
    {
        /**
         * @var User $user
         */
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);

        $user->deposit(1);
        $this->assertEquals($user->balance, 1);

        $user->withdraw(1, null, false);
        $this->assertEquals($user->balance, 1);

        $user->withdraw(1);
        $this->assertEquals($user->balance, 0);
    }

    /**
     * @return void
     */
    public function testRecalculate(): void
    {
        /**
         * @var User $user
         */
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);

        $user->deposit(100, null, false);
        $this->assertEquals($user->balance, 0);

        $user->transactions()->update(['confirmed' => true]);
        $this->assertEquals($user->balance, 0);

        $user->wallet->refreshBalance();
        $this->assertEquals($user->balance, 100);

        $user->withdraw($user->balance);
        $this->assertEquals($user->balance, 0);
    }

}
