<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Support\Str;

/**
 * @internal
 * @coversNothing
 */
class RateTest extends TestCase
{
    public function testRate(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'Dollar USA',
            'slug' => 'my-usd',
            'meta' => ['currency' => 'USD'],
        ]);

        self::assertEquals('my-usd', $usd->slug);
        self::assertEquals('USD', $usd->currency);
        self::assertEquals($usd->holder_id, $user->id);
        self::assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet(['name' => 'RUB']);
        self::assertEquals('rub', $rub->slug);
        self::assertEquals('RUB', $rub->currency);
        self::assertEquals($rub->holder_id, $user->id);
        self::assertInstanceOf($rub->holder_type, $user);

        $superWallet = $user->createWallet(['name' => 'Super Wallet']);
        self::assertEquals($superWallet->slug, Str::slug('Super Wallet'));
        self::assertEquals($superWallet->currency, Str::upper(Str::slug('Super Wallet')));
        self::assertEquals($superWallet->holder_id, $user->id);
        self::assertInstanceOf($superWallet->holder_type, $user);

        $rate = app(Rateable::class)
            ->withAmount(1000)
            ->withCurrency($usd)
            ->convertTo($rub)
        ;

        self::assertEquals(67610., $rate);
    }

    public function testExchange(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet([
            'name' => 'USD',
            'meta' => ['currency' => 'USD'],
        ]);

        self::assertEquals('usd', $usd->slug);
        self::assertEquals('USD', $usd->currency);
        self::assertEquals($usd->holder_id, $user->id);
        self::assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet([
            'name' => 'RUR',
            'slug' => 'my-rub',
            'meta' => ['currency' => 'RUB'],
        ]);

        self::assertEquals('my-rub', $rub->slug);
        self::assertEquals('RUB', $rub->currency);
        self::assertEquals($rub->holder_id, $user->id);
        self::assertInstanceOf($rub->holder_type, $user);

        $rate = app(ExchangeService::class)
            ->rate($usd, $rub)
        ;

        self::assertEquals(67.61, $rate);

        $rate = app(ExchangeService::class)
            ->rate($rub, $usd)
        ;

        self::assertEquals($rate, 1 / 67.61);
    }
}
