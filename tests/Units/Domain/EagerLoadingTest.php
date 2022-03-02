<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class EagerLoadingTest extends TestCase
{
    public function testUuidDuplicate(): void
    {
        $buyerTimes = BuyerFactory::times(10)->create();

        /** @var Buyer[] $buyers */
        $buyers = Buyer::with('wallet')
            ->whereIn('id', $buyerTimes->pluck('id')->toArray())
            ->paginate(10)
        ;

        $uuids = [];
        $balances = [];
        foreach ($buyers as $buyer) {
            $uuids[] = $buyer->wallet->uuid;
            $balances[] = $buyer->wallet->balanceInt;
        }

        self::assertCount(10, array_unique($uuids));
        self::assertCount(1, array_unique($balances));
    }
}
