<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Test\Common\Models\Transaction;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\Wallet\Test\Objects;

class WalletExtensionTest extends TestCase
{

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->app->bind(Operation::class, Objects\Operation::class);
    }

    /**
     * @return void
     */
    public function testCustomAttribute(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000, ['bank_method' => 'VietComBank']);

        self::assertEquals($transaction->amount, $buyer->balance);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals('VietComBank', $transaction->bank_method);
    }

    public function testNoCustomAttribute(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000);

        self::assertEquals($transaction->amount, $buyer->balance);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertNull($transaction->bank_method);
    }

}
