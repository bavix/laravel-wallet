<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Objects\EmptyLock;
use Bavix\Wallet\Services\AtomicService;

/**
 * @internal
 */
class EmptyLockTest extends TestCase
{
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

    public function testOwner(): void
    {
        $empty = app(EmptyLock::class);
        $str = $empty->owner();
        self::assertIsString($str);
        self::assertEquals($str, $empty->owner());
    }

    public function testAtomic(): void
    {
        $atomic = app(AtomicService::class);
        $atomic->block('hello', static fn () => 'hello world');
        $atomic->block('hello', static fn () => 'hello world');
        self::assertTrue(true);
    }
}
