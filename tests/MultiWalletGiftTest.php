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
        self::assertNull($first->getWallet('gifter'));

        $first->deposit(1);
        $second->deposit(2);

        $wallet = $first->createWallet(['name' => 'Gift', 'slug' => 'gifter']);
        self::assertNotNull($wallet);
        self::assertNotNull($first->wallet);
        self::assertNotNull($first->wallet->id, $wallet->id);

        /**
         * @var Item $item
         */
        $item = factory(Item::class)->create();
        $transaction = $wallet->deposit($item->getAmountProduct($wallet));
        self::assertEquals($transaction->amount, $wallet->balance);
        self::assertEquals($item->getAmountProduct($wallet), $wallet->balance);
        self::assertNotNull($transaction);

        $transfer = $wallet->gift($second, $item);
        self::assertNotNull($transfer);

        self::assertEquals($wallet->balance, 0);
        self::assertEquals($first->balance, 1);
        self::assertEquals($second->balance, 2);
        self::assertEquals($transfer->status, Transfer::STATUS_GIFT);

        self::assertEquals($transfer->withdraw->wallet->holder->id, $first->id);
        self::assertInstanceOf(UserMulti::class, $transfer->withdraw->wallet->holder);

        self::assertEquals($wallet->id, $transfer->withdraw->wallet->id);
        self::assertInstanceOf(Wallet::class, $transfer->withdraw->wallet);

        self::assertEquals($second->id, $transfer->from->holder_id);
        self::assertInstanceOf(UserMulti::class, $transfer->from->holder);

        self::assertFalse((bool)$wallet->paid($item));
        self::assertFalse((bool)$first->wallet->paid($item));
        self::assertFalse((bool)$second->wallet->paid($item));

        self::assertFalse((bool)$wallet->paid($item, true));
        self::assertFalse((bool)$first->wallet->paid($item, true));
        self::assertTrue((bool)$second->wallet->paid($item, true));
    }

}
