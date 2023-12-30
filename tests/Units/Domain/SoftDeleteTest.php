<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class SoftDeleteTest extends TestCase
{
    public function testDefaultWalletSoftDelete(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        self::assertFalse($buyer->wallet->exists);

        $buyer->deposit(1);

        $oldWallet = $buyer->wallet;

        self::assertTrue($buyer->wallet->exists);
        self::assertTrue($buyer->wallet->delete());
        self::assertNotNull($buyer->wallet->deleted_at);

        /** @var Buyer $buyer */
        $buyer = Buyer::query()->find($buyer->getKey());

        $buyer->deposit(2);

        self::assertNotSame($buyer->wallet->getKey(), $oldWallet->getKey());

        self::assertSame(1, $oldWallet->balanceInt);
        self::assertSame(2, $buyer->balanceInt);
    }

    public function testTransactionDelete(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        self::assertFalse($buyer->wallet->exists);

        $transaction = $buyer->deposit(1);

        self::assertTrue($buyer->wallet->exists);
        self::assertSame(1, $buyer->balanceInt);

        self::assertTrue($transaction->delete());

        self::assertSame(0, $buyer->balanceInt);
        self::assertFalse($transaction->confirmed);
    }

    public function testTransferDelete(): void
    {
        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        self::assertFalse($user1->relationLoaded('wallet'));
        self::assertFalse($user1->wallet->exists);

        self::assertFalse($user2->relationLoaded('wallet'));
        self::assertFalse($user2->wallet->exists);

        $transfer = $user1->forceTransfer($user2, 100);

        self::assertNotNull($transfer);
        self::assertSame(100, $transfer->deposit->amount);
        self::assertSame(-100, $transfer->withdraw->amount);

        self::assertSame(-100, $user1->balanceInt);
        self::assertSame(100, $user2->balanceInt);

        self::assertTrue($transfer->delete());

        self::assertSame(0, $user1->balanceInt);
        self::assertSame(0, $user2->balanceInt);
    }
}
