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
        $usd = $user->createWallet(['name' => 'USD']);
        $rub = $user->createWallet(['name' => 'RUB']);

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
        $rub = $user->createWallet(['name' => 'RUB']);

        $rate = app(ExchangeService::class)
            ->rate($usd, $rub);

        $this->assertEquals($rate, 67.61);

        $rate = app(ExchangeService::class)
            ->rate($rub, $usd);

        $this->assertEquals($rate, 1 / 67.61);
    }

}
