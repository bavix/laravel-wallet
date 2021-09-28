<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\UserMulti;

/**
 * @internal
 */
class ExchangeTest extends TestCase
{
    public function testSimple(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'Мои рубли',
            'slug' => 'rub',
        ]);

        self::assertEquals(0, $rub->balance);
        self::assertEquals(0, $usd->balance);

        $rub->deposit(10000);

        self::assertEquals(10000, $rub->balance);
        self::assertEquals(0, $usd->balance);

        $transfer = $rub->exchange($usd, 10000);
        self::assertEquals(0, $rub->balance);
        self::assertEquals(147, $usd->balance);
        self::assertEquals(1.47, $usd->balanceFloat); // $1.47
        self::assertEquals(0, $transfer->fee);
        self::assertEquals(Transfer::STATUS_EXCHANGE, $transfer->status);

        $transfer = $usd->exchange($rub, $usd->balance);
        self::assertEquals(0, $usd->balance);
        self::assertEquals(9938, $rub->balance);
        self::assertEquals(Transfer::STATUS_EXCHANGE, $transfer->status);
    }

    public function testSafe(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'Мои рубли',
            'slug' => 'rub',
        ]);

        self::assertEquals(0, $rub->balance);
        self::assertEquals(0, $usd->balance);

        $transfer = $rub->safeExchange($usd, 10000);
        self::assertNull($transfer);
    }
}
