<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Models\Item;
use Bavix\Wallet\Test\Models\UserMulti;

class MultiWalletGiftTest extends TestCase
{

    /**
     * @return void
     */
    public function testGiftWalletToUser(): void
    {
        /**
         * @var UserMulti $first
         * @var UserMulti $second
         */
        [$first, $second] = factory(UserMulti::class, 2)->create();
        $this->assertNull($first->getWallet('gifter'));

        $first->deposit(1);
        $second->deposit(2);

        $wallet = $first->createWallet(['name' => 'Gift', 'slug' => 'gifter']);
        $this->assertNotNull($wallet);
        $this->assertNotNull($first->wallet);
        $this->assertNotNull($first->wallet->id, $wallet->id);

        /**
         * @var Item $item
         */
        $item = factory(Item::class)->create();
        $transaction = $wallet->deposit($item->getAmountProduct($wallet));
        $this->assertEquals($transaction->amount, $wallet->balance);
        $this->assertEquals($item->getAmountProduct($wallet), $wallet->balance);
        $this->assertNotNull($transaction);

        $transfer = $wallet->gift($second, $item);
        $this->assertNotNull($transfer);

        $this->assertEquals($wallet->balance, 0);
        $this->assertEquals($first->balance, 1);
        $this->assertEquals($second->balance, 2);
        $this->assertEquals($transfer->status, Transfer::STATUS_GIFT);

        $this->assertEquals($transfer->withdraw->wallet->holder->id, $first->id);
        $this->assertInstanceOf(UserMulti::class, $transfer->withdraw->wallet->holder);

        $this->assertEquals($wallet->id, $transfer->withdraw->wallet->id);
        $this->assertInstanceOf(Wallet::class, $transfer->withdraw->wallet);

        $this->assertEquals($second->id, $transfer->from->holder_id);
        $this->assertInstanceOf(UserMulti::class, $transfer->from->holder);

        $this->assertFalse((bool)$wallet->paid($item));
        $this->assertFalse((bool)$first->wallet->paid($item));
        $this->assertFalse((bool)$second->wallet->paid($item));

        $this->assertFalse((bool)$wallet->paid($item, true));
        $this->assertFalse((bool)$first->wallet->paid($item, true));
        $this->assertTrue((bool)$second->wallet->paid($item, true));
    }

}
