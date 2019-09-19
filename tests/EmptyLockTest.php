<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Services\MakeService;

class EmptyLockTest extends TestCase
{

    /**
     * @return void
     */
    public function testSimple(): void
    {
        $empty = app(MakeService::class)->makeEmptyLock();
        $this->assertTrue($empty->block(1));
        $this->assertTrue($empty->block(1, null));
        $this->assertNull($empty->get());
        $this->assertTrue($empty->get(static function () {
            return true;
        }));
    }

    /**
     * @return void
     */
    public function testOwner(): void
    {
        $empty = app(MakeService::class)->makeEmptyLock();
        $str = $empty->owner();
        $this->assertIsString($str);
        $this->assertEquals($str, $empty->owner());
    }

}
