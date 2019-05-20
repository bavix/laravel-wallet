<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\ProxyService;
use function app;

class ProxyTest extends TestCase
{

    /**
     * @return void
     */
    public function testSimple(): void
    {
        $proxy = app(ProxyService::class);
        for ($i = 0; $i < 10; $i++) {
            $proxy[$i] = $i;
            $this->assertEquals($proxy[$i], $i);
            $proxy->remove($i);
            $this->assertEquals($proxy[$i], 0);
        }
    }

}
