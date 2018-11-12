<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Models\UserFloat as User;

class WalletFloatTest extends TestCase
{

    /**
     * @return void
     */
    public function testDeposit(): void
    {
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($user->balanceFloat, 0);

        $user->depositFloat(.1);
        $this->assertEquals($user->balance, 10);
        $this->assertEquals($user->balanceFloat, .1);

        $user->depositFloat(1.25);
        $this->assertEquals($user->balance, 135);
        $this->assertEquals($user->balanceFloat, 1.35);

        $user->deposit(865);
        $this->assertEquals($user->balance, 1000);
        $this->assertEquals($user->balanceFloat, 10);

        $this->assertEquals($user->transactions()->count(), 3);

        $user->withdraw($user->balance);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($user->balanceFloat, 0);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\AmountInvalid
     */
    public function testInvalidDeposit(): void
    {
        $user = factory(User::class)->create();
        $user->depositFloat(-1);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\BalanceIsEmpty
     */
    public function testWithdraw(): void
    {
        $user = factory(User::class)->create();
        $this->assertEquals($user->balance, 0);

        $user->depositFloat(1);
        $this->assertEquals($user->balanceFloat, 1);

        $user->withdrawFloat(.1);
        $this->assertEquals($user->balanceFloat, 0.9);

        $user->withdrawFloat(.81);
        $this->assertEquals($user->balanceFloat, .09);

        $user->withdraw(9);
        $this->assertEquals($user->balance, 0);

        $user->withdraw(1);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\AmountInvalid
     */
    public function testInvalidWithdraw(): void
    {
        $user = factory(User::class)->create();
        $user->withdrawFloat(-1);
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
        $this->assertEquals($first->balanceFloat, 0);
        $this->assertEquals($second->balanceFloat, 0);

        $first->deposit(100);
        $this->assertEquals($first->balanceFloat, 100);

        $second->deposit(100);
        $this->assertEquals($second->balanceFloat, 100);

        $first->transfer($second, 100);
        $this->assertEquals($first->balanceFloat, 0);
        $this->assertEquals($second->balanceFloat, 200);

        $second->transfer($first, 100);
        $this->assertEquals($second->balanceFloat, 100);
        $this->assertEquals($first->balanceFloat, 100);

        $second->transfer($first, 100);
        $this->assertEquals($second->balanceFloat, 0);
        $this->assertEquals($first->balanceFloat, 200);

        $first->withdraw($first->balanceFloat);
        $this->assertEquals($first->balanceFloat, 0);

        $this->assertNull($first->safeTransfer($second, 100));
        $this->assertEquals($first->balanceFloat, 0);
        $this->assertEquals($second->balanceFloat, 0);

        $this->assertNotNull($first->forceTransfer($second, 100));
        $this->assertEquals($first->balanceFloat, -100);
        $this->assertEquals($second->balanceFloat, 100);

        $this->assertNotNull($second->forceTransfer($first, 100));
        $this->assertEquals($first->balanceFloat, 0);
        $this->assertEquals($second->balanceFloat, 0);
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
     * @expectedException \Bavix\Wallet\Exceptions\BalanceIsEmpty
     */
    public function testBalanceIsEmpty(): void
    {
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

}
