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
     * @expectedException \Bavix\Wallet\Exceptions\BalanceIsEmpty
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

        $first->depositFloat(1);
        $this->assertEquals($first->balanceFloat, 1);

        $second->depositFloat(1);
        $this->assertEquals($second->balanceFloat, 1);

        $first->transferFloat($second, 1);
        $this->assertEquals($first->balanceFloat, 0);
        $this->assertEquals($second->balanceFloat, 2);

        $second->transferFloat($first, 1);
        $this->assertEquals($second->balanceFloat, 1);
        $this->assertEquals($first->balanceFloat, 1);

        $second->transferFloat($first, 1);
        $this->assertEquals($second->balanceFloat, 0);
        $this->assertEquals($first->balanceFloat, 2);

        $first->withdrawFloat($first->balanceFloat);
        $this->assertEquals($first->balanceFloat, 0);

        $this->assertNull($first->safeTransferFloat($second, 1));
        $this->assertEquals($first->balanceFloat, 0);
        $this->assertEquals($second->balanceFloat, 0);

        $this->assertNotNull($first->forceTransferFloat($second, 1));
        $this->assertEquals($first->balanceFloat, -1);
        $this->assertEquals($second->balanceFloat, 1);

        $this->assertNotNull($second->forceTransferFloat($first, 1));
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
        $this->assertEquals($user->balanceFloat, 0);

        $user->depositFloat(1);
        $user->transferFloat($user, 1);
        $this->assertEquals($user->balance, 100);

        $user->withdrawFloat($user->balanceFloat);
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
        $user->withdrawFloat(1);
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

        $user->depositFloat(1);
        $this->assertEquals($user->balanceFloat, 1);

        $user->withdrawFloat(1, null, false);
        $this->assertEquals($user->balanceFloat, 1);

        $this->assertTrue($user->canWithdrawFloat(1));
        $user->withdrawFloat(1);
        $this->assertFalse($user->canWithdrawFloat(1));
        $user->forceWithdrawFloat(1);
        $this->assertEquals($user->balanceFloat, -1);
        $user->depositFloat(1);
        $this->assertEquals($user->balanceFloat, 0);
    }

}
