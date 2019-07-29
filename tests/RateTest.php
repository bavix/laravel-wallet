<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Test\Models\UserMulti;
use Illuminate\Support\Str;

class RateTest extends TestCase
{

    /**
     * @return void
     */
    public function testRate(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $usd = $user->createWallet(['name' => 'Dollar USA', 'slug' => 'my-usd']);
        $this->assertEquals($usd->slug, 'my-usd');
        $this->assertEquals($usd->currency, 'USD');
        $this->assertEquals($usd->holder_id, $user->id);
        $this->assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet(['name' => 'RUB']);
        $this->assertEquals($rub->slug, 'rub');
        $this->assertEquals($rub->currency, 'RUB');
        $this->assertEquals($rub->holder_id, $user->id);
        $this->assertInstanceOf($rub->holder_type, $user);

        $superWallet = $user->createWallet(['name' => 'Super Wallet']);
        $this->assertEquals($superWallet->slug, Str::slug('Super Wallet'));
        $this->assertEquals($superWallet->currency, Str::upper(Str::slug('Super Wallet')));
        $this->assertEquals($superWallet->holder_id, $user->id);
        $this->assertInstanceOf($superWallet->holder_type, $user);

        $rate = app(Rateable::class)
            ->withAmount(1000)
            ->withCurrency($usd)
            ->convertTo($rub);

        $this->assertEquals($rate, 67610.);
    }

    /**
     * @return void
     */
    public function testExchange(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $usd = $user->createWallet(['name' => 'USD']);
        $this->assertEquals($usd->slug, 'usd');
        $this->assertEquals($usd->currency, 'USD');
        $this->assertEquals($usd->holder_id, $user->id);
        $this->assertInstanceOf($usd->holder_type, $user);

        $rub = $user->createWallet(['name' => 'RUR', 'slug' => 'my-rub']);
        $this->assertEquals($rub->slug, 'my-rub');
        $this->assertEquals($rub->currency, 'RUB');
        $this->assertEquals($rub->holder_id, $user->id);
        $this->assertInstanceOf($rub->holder_type, $user);

        $rate = app(ExchangeService::class)
            ->rate($usd, $rub);

        $this->assertEquals($rate, 67.61);

        $rate = app(ExchangeService::class)
            ->rate($rub, $usd);

        $this->assertEquals($rate, 1 / 67.61);
    }

}
