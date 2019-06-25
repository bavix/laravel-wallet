<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Test\Models\UserMulti;

class ExchangeTest extends TestCase
{

    /**
     * @return void
     */
    public function testSimple(): void
    {
        /**
         * @var UserMulti $user
         */
        $user = factory(UserMulti::class)->create();
        $usd = $user->createWallet([
            'name' => 'My USD',
            'slug' => 'usd',
        ]);

        $rub = $user->createWallet([
            'name' => 'Мои рубли',
            'slug' => 'rub',
        ]);

        $this->assertEquals($rub->balance, 0);
        $this->assertEquals($usd->balance, 0);

        $rub->deposit(10000);

        $this->assertEquals($rub->balance, 10000);
        $this->assertEquals($usd->balance, 0);

        $rub->exchange($usd, 10000);
        $this->assertEquals($rub->balance, 0);
        $this->assertEquals($usd->balance, 147);
        $this->assertEquals($usd->balanceFloat, 1.47); // $1.47

        $usd->exchange($rub, $usd->balance);
        $this->assertEquals($usd->balance, 0);
        $this->assertEquals($rub->balance, 9938);
    }

}
