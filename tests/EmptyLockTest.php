<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Objects\EmptyLock;

class EmptyLockTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimple(): void
    {
        $empty = app(EmptyLock::class);
        self::assertTrue($empty->block(1));
        self::assertTrue($empty->block(1, null));
        self::assertNull($empty->get());
        self::assertTrue($empty->get(static function () {
            return true;
        }));
    }

    /**
     * @return void
     */
    public function testOwner(): void
    {
        $empty = app(EmptyLock::class);
        $str = $empty->owner();
        self::assertIsString($str);
        self::assertEquals($str, $empty->owner());
    }
}
