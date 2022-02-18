<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
class EagerLoadingTest extends TestCase
{
    public function testUuidDuplicate(): void
    {
        BuyerFactory::times(10)->create();

        /** @var Buyer[] $buyers */
        $buyers = Buyer::with('wallet')->paginate(10);

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
