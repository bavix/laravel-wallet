<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Database\Eloquent\Collection;

/**
 * @internal
 */
final class EagerLoadingTest extends TestCase
{
    public function testUuidDuplicate(): void
    {
        /** @var Buyer[]|Collection $buyerTimes */
        $buyerTimes = BuyerFactory::times(10)->create();
        foreach ($buyerTimes as $buyerTime) {
            $buyerTime->deposit(100);
        }

        /** @var Buyer[] $buyers */
        $buyers = Buyer::with('wallet')
            ->whereIn('id', $buyerTimes->pluck('id')->toArray())
            ->paginate(10)
        ;

        $uuids = [];
        $balances = [];
        foreach ($buyers as $buyer) {
            self::assertTrue($buyer->relationLoaded('wallet'));

            $uuids[] = $buyer->wallet->uuid;
            $balances[] = $buyer->wallet->balanceInt;
        }

        self::assertCount(10, array_unique($uuids));
        self::assertCount(1, array_unique($balances));
    }

    public function testTransferTransactions(): void
    {
        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        $user1->deposit(1000);
        self::assertSame(1000, $user1->balanceInt);

        $transfer = $user1->transfer($user2, 500);
        self::assertTrue($transfer->relationLoaded('withdraw'));
        self::assertTrue($transfer->relationLoaded('deposit'));
    }
}
