<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Simple\Exchange;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Support\Str;

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

    public function testExchangeClass(): void
    {
        $service = app(Exchange::class);

        self::assertEquals(1, $service->convertTo('USD', 'EUR', 1));
        self::assertEquals(5, $service->convertTo('USD', 'EUR', 5));
        self::assertEquals(27, $service->convertTo('USD', 'EUR', 27));
    }

    public function testRate(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet(['name' => 'Dollar USA', 'slug' => 'my-usd', 'meta' => ['currency' => 'USD']]);
        self::assertEquals($usd->slug, 'my-usd');
        self::assertEquals($usd->currency, 'USD');
        self::assertEquals($usd->holder_id, $user->id);
        self::assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet(['name' => 'RUB']);
        self::assertEquals($rub->slug, 'rub');
        self::assertEquals($rub->currency, 'RUB');
        self::assertEquals($rub->holder_id, $user->id);
        self::assertInstanceOf($rub->holder_type, $user);

        $superWallet = $user->createWallet(['name' => 'Super Wallet']);
        self::assertEquals($superWallet->slug, Str::slug('Super Wallet'));
        self::assertEquals($superWallet->currency, Str::upper(Str::slug('Super Wallet')));
        self::assertEquals($superWallet->holder_id, $user->id);
        self::assertInstanceOf($superWallet->holder_type, $user);

        $rate = app(ExchangeInterface::class)
            ->convertTo($usd->currency, $rub->currency, 1000)
        ;

        self::assertEquals(67610., $rate);
    }

    public function testExchange(): void
    {
        $rate = app(ExchangeInterface::class)
            ->convertTo('USD', 'RUB', 1)
        ;

        self::assertEquals(67.61, $rate);

        $rate = app(ExchangeInterface::class)
            ->convertTo('RUB', 'USD', 1)
        ;

        self::assertEquals(1 / 67.61, $rate);
    }
}
