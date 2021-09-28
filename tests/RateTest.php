<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Test\Factories\UserMultiFactory;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Support\Str;

/**
 * @internal
 */
class RateTest extends TestCase
{
    public function testRate(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet(['name' => 'Dollar USA', 'slug' => 'my-usd']);
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

        $rate = app(Rateable::class)
            ->withAmount(1000)
            ->withCurrency($usd)
            ->convertTo($rub)
        ;

        self::assertEquals($rate, 67610.);
    }

    public function testExchange(): void
    {
        /** @var UserMulti $user */
        $user = UserMultiFactory::new()->create();
        $usd = $user->createWallet(['name' => 'USD']);
        self::assertEquals($usd->slug, 'usd');
        self::assertEquals($usd->currency, 'USD');
        self::assertEquals($usd->holder_id, $user->id);
        self::assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet(['name' => 'RUR', 'slug' => 'my-rub']);
        self::assertEquals($rub->slug, 'my-rub');
        self::assertEquals($rub->currency, 'RUB');
        self::assertEquals($rub->holder_id, $user->id);
        self::assertInstanceOf($rub->holder_type, $user);

        $rate = app(ExchangeService::class)
            ->rate($usd, $rub)
        ;

        self::assertEquals($rate, 67.61);

        $rate = app(ExchangeService::class)
            ->rate($rub, $usd)
        ;

        self::assertEquals($rate, 1 / 67.61);
    }
}
