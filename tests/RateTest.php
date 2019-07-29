<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Test\Models\UserMulti;

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

        $rub = $user->createWallet(['name' => 'RUB']);
        $this->assertEquals($rub->slug, 'rub');
        $this->assertEquals($rub->currency, 'RUB');

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

        $rub = $user->createWallet(['name' => 'RUR', 'slug' => 'my-rub']);
        $this->assertEquals($rub->slug, 'my-rub');
        $this->assertEquals($rub->currency, 'RUB');

        $rate = app(ExchangeService::class)
            ->rate($usd, $rub);

        $this->assertEquals($rate, 67.61);

        $rate = app(ExchangeService::class)
            ->rate($rub, $usd);

        $this->assertEquals($rate, 1 / 67.61);
    }

}
