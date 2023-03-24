<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Test\Infra\Factories\ItemFactory;
use Bavix\Wallet\Test\Infra\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Infra\Models\Item;
use Bavix\Wallet\Test\Infra\Models\UserMulti;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class MultiWalletGiftTest extends TestCase
{
    public function testGiftWalletToUser(): void
    {
        /**
         * @var UserMulti $first
         * @var UserMulti $second
         */
        [$first, $second] = UserMultiFactory::times(2)->create();
        self::assertNull($first->getWallet('gifter'));

        $first->deposit(1);
        $second->deposit(2);

        $wallet = $first->createWallet([
            'name' => 'Gift',
            'slug' => 'gifter',
        ]);
        self::assertNotNull($wallet);
        self::assertNotNull($first->wallet);
        self::assertNotSame((int) $first->wallet->getKey(), (int) $wallet->getKey());

        /** @var Item $item */
        $item = ItemFactory::new()->create();
        $transaction = $wallet->deposit($item->getAmountProduct($wallet));
        self::assertSame($transaction->amountInt, $wallet->balanceInt);
        self::assertSame($item->getAmountProduct($wallet), $wallet->balanceInt);
        self::assertNotNull($transaction);

        $transfer = $wallet->gift($second, $item);
        self::assertNotNull($transfer);

        self::assertSame($wallet->balanceInt, 0);
        self::assertSame($first->balanceInt, 1);
        self::assertSame($second->balanceInt, 2);
        self::assertSame($transfer->status, Transfer::STATUS_GIFT);

        /** @var string $holderKey */
        $holderKey = $transfer->withdraw->wallet->holder->getKey();

        self::assertSame((int) $first->getKey(), (int) $holderKey);
        self::assertInstanceOf(UserMulti::class, $transfer->withdraw->wallet->holder);

        self::assertSame((int) $wallet->getKey(), (int) $transfer->withdraw->wallet->getKey());
        self::assertInstanceOf(Wallet::class, $transfer->withdraw->wallet);

        self::assertSame((int) $second->getKey(), (int) $transfer->from->holder_id);
        self::assertInstanceOf(UserMulti::class, $transfer->from->holder);

        self::assertFalse((bool) $wallet->paid($item));
        self::assertFalse((bool) $first->wallet->paid($item));
        self::assertFalse((bool) $second->wallet->paid($item));

        self::assertFalse((bool) $wallet->paid($item, true));
        self::assertFalse((bool) $first->wallet->paid($item, true));
        self::assertTrue((bool) $second->wallet->paid($item, true));
    }
}
