<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Models\UserMulti;

class MultiWalletTest extends TestCase
{

    /**
     * @return void
     */
    public function testDeposit(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(10);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 10);

        $wallet->deposit(125);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 135);

        $wallet->deposit(865);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 1000);

        $this->assertEquals($user->transactions()->count(), 3);

        $wallet->withdraw($wallet->balance);
        $this->assertEquals($user->balance, 0);
        $this->assertEquals($wallet->balance, 0);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\AmountInvalid
     */
    public function testInvalidDeposit(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $wallet->deposit(-1);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\BalanceIsEmpty
     */
    public function testWithdraw(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        $this->assertEquals($wallet->balance, 100);

        $wallet->withdraw(10);
        $this->assertEquals($wallet->balance, 90);

        $wallet->withdraw(81);
        $this->assertEquals($wallet->balance, 9);

        $wallet->withdraw(9);
        $this->assertEquals($wallet->balance, 0);

        $wallet->withdraw(1);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\BalanceIsEmpty
     */
    public function testInvalidWithdraw(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $wallet->withdraw(-1);
    }

    /**
     * @return void
     */
    public function testTransfer(): void
    {
        /**
         * @var UserMulti $first
         * @var UserMulti $second
         */
        list($first, $second) = factory(UserMulti::class, 2)->create();
        $firstWallet = $first->createWallet([
            'name' => 'deposit'
        ]);

        $secondWallet = $second->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertNotEquals($first->id, $second->id);
        $this->assertNotEquals($firstWallet->id, $secondWallet->id);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($secondWallet->balance, 0);

        $firstWallet->deposit(100);
        $this->assertEquals($firstWallet->balance, 100);

        $secondWallet->deposit(100);
        $this->assertEquals($secondWallet->balance, 100);

        $transfer = $firstWallet->transfer($secondWallet, 100);
        $this->assertEquals($first->balance, 0);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($second->balance, 0);
        $this->assertEquals($secondWallet->balance, 200);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        $this->assertEquals($secondWallet->balance, 100);
        $this->assertEquals($firstWallet->balance, 100);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->transfer($firstWallet, 100);
        $this->assertEquals($secondWallet->balance, 0);
        $this->assertEquals($firstWallet->balance, 200);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $firstWallet->withdraw($firstWallet->balance);
        $this->assertEquals($firstWallet->balance, 0);

        $this->assertNull($firstWallet->safeTransfer($secondWallet, 100));
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($secondWallet->balance, 0);

        $transfer = $firstWallet->forceTransfer($secondWallet, 100);
        $this->assertNotNull($transfer);
        $this->assertEquals($firstWallet->balance, -100);
        $this->assertEquals($secondWallet->balance, 100);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);

        $transfer = $secondWallet->forceTransfer($firstWallet, 100);
        $this->assertNotNull($transfer);
        $this->assertEquals($firstWallet->balance, 0);
        $this->assertEquals($secondWallet->balance, 0);
        $this->assertEquals($transfer->status, Transfer::STATUS_TRANSFER);
    }

    /**
     * @return void
     */
    public function testTransferYourself(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(100);
        $wallet->transfer($wallet, 100);
        $this->assertEquals($wallet->balance, 100);

        $wallet->withdraw($wallet->balance);
        $this->assertEquals($wallet->balance, 0);
    }

    /**
     * @return void
     * @expectedException \Bavix\Wallet\Exceptions\BalanceIsEmpty
     */
    public function testBalanceIsEmpty(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);
        $wallet->withdraw(1);
    }

    /**
     * @return void
     */
    public function testConfirmed(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $wallet = $user->createWallet([
            'name' => 'deposit'
        ]);

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(1);
        $this->assertEquals($wallet->balance, 1);

        $wallet->withdraw(1, null, false);
        $this->assertEquals($wallet->balance, 1);

        $wallet->withdraw(1);
        $this->assertEquals($wallet->balance, 0);
    }

    /**
     * @expectedException \Illuminate\Database\QueryException
     */
    public function testWalletUnique(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();

        $user->createWallet([
            'name' => 'deposit'
        ]);

        $user->createWallet([
            'name' => 'deposit'
        ]);
    }

    /**
     * @return void
     */
    public function testGetWallet(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();

        $firstWallet = $user->createWallet([
            'name' => 'My Test',
            'slug' => 'test',
        ]);

        $secondWallet = $user->getWallet('test');
        $this->assertEquals($secondWallet->getKey(), $firstWallet->getKey());

        $test2 = $user->wallets()->create([
            'name' => 'Test2'
        ]);

        $this->assertEquals(
            $test2->getKey(),
            $user->getWallet('test2')->getKey()
        );
    }

}
