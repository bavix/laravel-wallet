<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Test\Models\Buyer;
use function app;

class BalanceTest extends TestCase
{

    /**
     * @return void
     */
    public function testSimple(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = factory(Buyer::class)->create();
        $wallet = $buyer->wallet;

        $this->assertEquals($wallet->balance, 0);

        $wallet->deposit(1000);
        $this->assertEquals($wallet->balance, 1000);

        $result = app(CommonService::class)->addBalance($wallet, 100);
        $this->assertTrue($result);

        $this->assertEquals($wallet->balance, 1100);
        $this->assertTrue($wallet->refreshBalance());

        $this->assertEquals($wallet->balance, 1000);

        $this->assertTrue($wallet->delete());
        $this->assertFalse($wallet->exists);
        $result = app(CommonService::class)->addBalance($wallet, 100);
        $this->assertTrue($result); // automatic create default wallet
    }

}
