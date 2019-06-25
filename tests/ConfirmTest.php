<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Models\Buyer;

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

        $wallet->confirm($transaction);
        $this->assertEquals($wallet->balance, $transaction->amount);
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

        $wallet->safeConfirm($transaction);
        $this->assertEquals($wallet->balance, 0);
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

        $wallet->forceConfirm($transaction);
        $this->assertEquals($wallet->balance, $transaction->amount);
    }

}
