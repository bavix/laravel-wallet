<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Models\User;

class CastsTest extends TestCase
{

    /**
     * @return void
     */
    public function testModelWallet(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $this->assertEquals($buyer->balance, 0);

        $this->assertIsInt($buyer->wallet->getKey());
        $this->assertEquals($buyer->wallet->getCasts()['id'], 'int');

        config(['wallet.wallet.casts.id' => 'string']);
        $this->assertIsString($buyer->wallet->getKey());
        $this->assertEquals($buyer->wallet->getCasts()['id'], 'string');
    }

    /**
     * @return void
     */
    public function testModelTransfer(): void
    {
        /**
         * @var Buyer $buyer
         * @var User $user
         */
        $buyer = factory(Buyer::class)->create();
        $user = factory(User::class)->create();
        $this->assertEquals($buyer->balance, 0);
        $this->assertEquals($user->balance, 0);

        $deposit = $user->deposit(1000);
        $this->assertEquals($user->balance, $deposit->amount);

        $transfer = $user->transfer($buyer, 700);

        $this->assertIsInt($transfer->getKey());
        $this->assertEquals($transfer->getCasts()['id'], 'int');

        config(['wallet.transfer.casts.id' => 'string']);
        $this->assertIsString($transfer->getKey());
        $this->assertEquals($transfer->getCasts()['id'], 'string');
    }

    /**
     * @return void
     */
    public function testModelTransaction(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $this->assertEquals($buyer->balance, 0);
        $deposit = $buyer->deposit(1);

        $this->assertIsInt($deposit->getKey());
        $this->assertEquals($deposit->getCasts()['id'], 'int');

        config(['wallet.transaction.casts.id' => 'string']);
        $this->assertIsString($deposit->getKey());
        $this->assertEquals($deposit->getCasts()['id'], 'string');
    }

}
